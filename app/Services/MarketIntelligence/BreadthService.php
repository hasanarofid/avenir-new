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

        // ---- 2. Sector Rotation (uses pre-built sector_master.csv) ---------
        $sectorMasterCsv = storage_path('app/sector_master.csv');
        if (file_exists($sectorMasterCsv)) {
            $srOutDir = $tempDir . "/sr_{$date}";
            @mkdir($srOutDir, 0755, true);
            $srJson = $srOutDir . '/latest_sector_rotation_score.json';

            $srArgs = [
                '--stocks'        => $excelPath,
                '--sector-master' => $sectorMasterCsv,
                '--output-dir'    => $srOutDir,
            ];
            
            if (!empty($indexFullPath)) {
                $srArgs['--index-summary'] = $indexFullPath;
            }

            $srPayload = $this->bridge->run('sector_rotation.py', $srArgs, $srJson);

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
