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
            
            // Extract Top Movers from market_breadth_stock_detail.csv
            $mbCsvPath = $mbOutDir . '/market_breadth_stock_detail.csv';
            if (file_exists($mbCsvPath)) {
                $stocks = [];
                if (($handle = fopen($mbCsvPath, 'r')) !== false) {
                    $header = fgetcsv($handle);
                    while (($row = fgetcsv($handle)) !== false) {
                        $data = array_combine($header, $row);
                        if (isset($data['symbol']) && isset($data['return_pct'])) {
                            $pct = (float)$data['return_pct'] * 100;
                            // Ensure realistic constraints for valid movers
                            if ($pct > -100 && $pct < 500) {
                                $stocks[] = [
                                    'symbol' => $data['symbol'],
                                    'price_pct' => round($pct, 2),
                                    'last_close' => $data['close'],
                                    'flow_confirmed' => true // Default true, can be enhanced with Foreign Flow later
                                ];
                            }
                        }
                    }
                    fclose($handle);
                }
                
                usort($stocks, fn($a, $b) => $b['price_pct'] <=> $a['price_pct']);
                $gainers = array_filter($stocks, fn($s) => $s['price_pct'] > 0);
                $gainers = array_slice($gainers, 0, 20);
                
                usort($stocks, fn($a, $b) => $a['price_pct'] <=> $b['price_pct']);
                $losers = array_filter($stocks, fn($s) => $s['price_pct'] < 0);
                $losers = array_slice($losers, 0, 20);
                
                $topMoversPayload = [
                    'gainers' => array_values($gainers),
                    'losers' => array_values($losers)
                ];
                
                MarketSnapshot::updateOrCreate(
                    ['date' => $date, 'symbol_or_metric' => 'TOP_MOVERS'],
                    ['value' => 0, 'sparkline_json' => $topMoversPayload, 'source' => 'ringkasan_saham']
                );
            }

            $results['market_breadth'] = $mbPayload;
        }

        // ---- 2. Sector Rotation (uses pre-built sector_master.csv) ---------
        $sectorMasterCsv = storage_path('app/sector_master.csv');
        if (file_exists($sectorMasterCsv)) {
            $srOutDir = $tempDir . "/sr_{$date}";
            @mkdir($srOutDir, 0755, true);
            $srJson = $srOutDir . '/latest_sector_rotation_score.json';
            $srPayload = $this->bridge->run('sector_rotation.py', [
                '--stocks'        => $excelPath,
                '--sector-master' => $sectorMasterCsv,
                '--output-dir'    => $srOutDir,
            ], $srJson);

            if ($srPayload) {
                $score = (int) ($srPayload['sector_rotation_score'] ?? 0);
                MarketSnapshot::updateOrCreate(
                    ['date' => $date, 'symbol_or_metric' => 'SR_SCORE'],
                    ['value' => $score, 'source' => 'ringkasan_saham']
                );
                $results['sector_rotation'] = $srPayload;
            }
        }

        return $results;
    }
}
