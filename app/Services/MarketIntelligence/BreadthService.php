<?php

namespace App\Services\MarketIntelligence;

use App\Models\MarketSnapshot;

/**
 * BreadthService: Runs market_breadth.py and sector_rotation.py
 * against uploaded Ringkasan Saham file. Stores results in market_snapshots.
 */
class BreadthService
{
    public function __construct(private PythonBridge $bridge) {}

    /**
     * Calculate and store Market Breadth + Sector Rotation scores
     * from a Ringkasan Saham Excel file.
     *
     * @param string $date       Trading date (Y-m-d)
     * @param string $excelPath  Absolute path to Ringkasan Saham .xlsx
     * @param string $masterPath Absolute path to Financial Data masterlist .xlsx
     */
    public function calculateAndStore(string $date, string $excelPath, string $masterPath = '', string $indexFullPath = ''): array
    {
        $tempDir = $this->bridge->getTempDir();
        $results = [];

        // ---- 1. Market Breadth ----------------------------------------
        $mbOutDir  = $tempDir . "/mb_{$date}";
        @mkdir($mbOutDir, 0755, true);
        $mbJson = $mbOutDir . '/latest_market_breadth_score.json';

        $mbPayload = $this->bridge->run('market_breadth.py', [
            '--input'      => $excelPath,
            '--output-dir' => $mbOutDir,
        ], $mbJson);

        if ($mbPayload) {
            $score = (int) ($mbPayload['market_breadth_score'] ?? 0);
            MarketSnapshot::updateOrCreate(
                ['date' => $date, 'symbol_or_metric' => 'BREADTH_SCORE'],
                ['value' => $score, 'source' => 'ringkasan_saham']
            );
            MarketSnapshot::updateOrCreate(
                ['date' => $date, 'symbol_or_metric' => 'MB_SCORE'],
                ['value' => $score, 'source' => 'ringkasan_saham']
            );
            // Sub-scores for transparency
            foreach (['ad_score','strong_movers_score','mcap_breadth_score','value_breadth_score','active_participation_score'] as $k) {
                if (isset($mbPayload[$k])) {
                    MarketSnapshot::updateOrCreate(
                        ['date' => $date, 'symbol_or_metric' => 'MB_' . strtoupper($k)],
                        ['value' => $mbPayload[$k], 'source' => 'ringkasan_saham']
                    );
                }
            }
            // Advancers / Decliners / Stable from detailed breadth
            if (isset($mbPayload['advancers'])) {
                MarketSnapshot::updateOrCreate(
                    ['date' => $date, 'symbol_or_metric' => 'ADVANCERS'],
                    ['value' => $mbPayload['advancers'], 'source' => 'ringkasan_saham']
                );
            }
            if (isset($mbPayload['decliners'])) {
                MarketSnapshot::updateOrCreate(
                    ['date' => $date, 'symbol_or_metric' => 'DECLINERS'],
                    ['value' => $mbPayload['decliners'], 'source' => 'ringkasan_saham']
                );
            }
            if (isset($mbPayload['stable'])) {
                MarketSnapshot::updateOrCreate(
                    ['date' => $date, 'symbol_or_metric' => 'STABLE'],
                    ['value' => $mbPayload['stable'], 'source' => 'ringkasan_saham']
                );
            }
            $results['market_breadth'] = $mbPayload;
        }

        // ---- 2. Sector Rotation (using new client formula) ---------
        if (!empty($indexFullPath)) {
            $srOutDir = $tempDir . "/sr_{$date}";
            @mkdir($srOutDir, 0755, true);
            
            // 2.1 Parse Index Summary and save to MarketSnapshot
            $parsedJson = $srOutDir . '/parsed_sectors.json';
            $parsedPayload = $this->bridge->run('parse_index_summary_sectors.py', [
                '--input' => $indexFullPath,
                '--output' => $parsedJson,
                '--date' => $date
            ], $parsedJson);

            if ($parsedPayload) {
                foreach ($parsedPayload as $sectorName => $returnVal) {
                    // Map "Energy" back to IDXENERGY for MarketSnapshot if we want to store it,
                    // but the new python script expects columns like "Energy". 
                    // Let's store it in MarketSnapshot as "SECTOR_Energy" to avoid overlap.
                    MarketSnapshot::updateOrCreate(
                        ['date' => $date, 'symbol_or_metric' => 'SECTOR_' . $sectorName],
                        ['value' => $returnVal, 'source' => 'index_summary']
                    );
                }
            }

            // 2.2 Generate sector_returns.csv containing up to 10 days of history
            $sectorReturnsCsv = $srOutDir . '/sector_returns.csv';
            
            $sectors = [
                "Energy", "Basic Materials", "Industrials", "Consumer Non-Cyclicals",
                "Consumer Cyclicals", "Healthcare", "Financials", "Properties & Real Estate",
                "Technology", "Infrastructures", "Transportation & Logistic"
            ];
            
            // Fetch historical dates from both MarketSnapshot and SectorBiasDaily
            $pastDates1 = MarketSnapshot::where('symbol_or_metric', 'like', 'SECTOR_%')
                ->where('date', '<=', $date)
                ->pluck('date')
                ->map(fn($d) => substr((string)$d, 0, 10))
                ->toArray();
                
            $pastDates2 = \App\Models\SectorBiasDaily::where('date', '<=', $date)
                ->pluck('date')
                ->map(fn($d) => substr((string)$d, 0, 10))
                ->toArray();

            $allDates = array_unique(array_merge($pastDates1, $pastDates2));
            rsort($allDates);
            $allDates = array_slice($allDates, 0, 10);
            sort($allDates); // chronological order
            
            $fp = fopen($sectorReturnsCsv, 'w');
            $headers = array_merge(['date'], $sectors);
            fputcsv($fp, $headers);
            
            foreach ($allDates as $d) {
                $row = [$d];
                foreach ($sectors as $s) {
                    $snap = MarketSnapshot::whereDate('date', $d)->where('symbol_or_metric', 'SECTOR_' . $s)->first();
                    if ($snap) {
                        $row[] = $snap->value;
                    } else {
                        $bias = \App\Models\SectorBiasDaily::whereDate('date', $d)->where('sector', $s)->first();
                        $row[] = $bias ? $bias->return_1d : '';
                    }
                }
                fputcsv($fp, $row);
            }
            fclose($fp);
            
            // 2.3 Run sector_rotation.py
            $srJson = $srOutDir . '/latest_sector_rotation_score.json';
            $srPayload = $this->bridge->run('sector_rotation.py', [
                '--input' => $sectorReturnsCsv,
                '--output-dir' => $srOutDir,
            ], $srJson);

            if ($srPayload) {
                $score = (int) ($srPayload['sector_rotation_score'] ?? 0);
                MarketSnapshot::updateOrCreate(
                    ['date' => $date, 'symbol_or_metric' => 'SR_SCORE'],
                    ['value' => $score, 'source' => 'index_summary']
                );
                $results['sector_rotation'] = $srPayload;
            }
        }

        return $results;
    }
}
