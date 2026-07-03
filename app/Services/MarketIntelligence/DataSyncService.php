<?php

namespace App\Services\MarketIntelligence;

use App\Models\MarketSnapshot;
use App\Models\SectorBiasDaily;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DataSyncService
{
    /**
     * Sinkronisasi data dari Sectors.app dan FRED/Makro
     */
    public function syncAll(string $date = null)
    {
        $date = $date ?? today()->toDateString();
        
        Log::info("[DataSyncService] Starting sync for date: {$date}");
        
        $this->syncSectorsApp($date);
        $this->syncMacroData($date);
        
        Log::info("[DataSyncService] Sync completed for date: {$date}");
    }

    /**
     * Sinkronisasi dari Sectors API
     */
    protected function syncSectorsApp(string $date)
    {
        $sectorsApi = app(\App\Services\SectorsApiService::class);

        // 1. Fetch IHSG, LQ45, IDX30
        $indexSnapshots = $sectorsApi->fetchIndexSnapshots();
        if (!empty($indexSnapshots)) {
            $sectorsApi->persistIndexSnapshots($indexSnapshots);
        }

        // 2. Fetch Sector Indices
        $sectorIndices = $sectorsApi->fetchSectorIndices();
        foreach ($sectorIndices as $secData) {
            $ret1d = $secData['return_1d'];
            $rot = $ret1d > 1 ? 1 : ($ret1d < -1 ? -1 : 0);
            $sm = $ret1d > 0.5 ? 1 : ($ret1d < -0.5 ? -1 : 0);
            $flow = $ret1d * 10000;

            SectorBiasDaily::updateOrCreate(
                ['date' => $secData['date'], 'sector' => $secData['sector']],
                [
                    'return_1d' => $ret1d,
                    'bias' => $ret1d > 0 ? 'bullish' : 'bearish',
                    'flow_value' => $flow,
                    'rotation_score' => $rot,
                    'smart_money_score' => $sm,
                    'valuation_score' => 0, // Pending actual data source
                    'event_score' => 0,     // Pending actual data source
                ]
            );
        }
    }

    /**
     * Sinkronisasi dari FRED/Makro (mock/simulated for MVP since no API key provided)
     */
    protected function syncMacroData(string $date)
    {
        $fredApiKey = env('FRED_API_KEY');
        
        $macros = [
            'GLOBAL_GROWTH' => ['val' => 3.1, 'chg' => 0],
            'US_INFLATION' => ['val' => 2.3, 'chg' => -0.1],
            'G3_LIQUIDITY' => ['val' => 6.1, 'chg' => 0.2]
        ];

        // Fetch DEXINUS (Used as proxy for USD/IDR as per PRD) from FRED if API Key exists
        if ($fredApiKey) {
            try {
                $response = Http::timeout(10)->get("https://api.stlouisfed.org/fred/series/observations", [
                    'series_id' => 'DEXINUS',
                    'api_key' => $fredApiKey,
                    'file_type' => 'json',
                    'sort_order' => 'desc',
                    'limit' => 6 // Need current + 5 days ago to calculate 5d change
                ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['observations']) && count($data['observations']) >= 2) {
                        $obs = $data['observations'];
                        // Find the most recent valid value
                        $currentVal = null;
                        $pastVal = null;
                        
                        foreach ($obs as $o) {
                            if (is_numeric($o['value'])) {
                                if ($currentVal === null) {
                                    $currentVal = (float) $o['value'];
                                } else {
                                    // Store older values to find 5d change
                                    $pastVal = (float) $o['value'];
                                }
                            }
                        }

                        if ($currentVal !== null && $pastVal !== null) {
                            $pctChange = ($currentVal - $pastVal) / $pastVal;
                            
                            MarketSnapshot::updateOrCreate(
                                ['date' => $date, 'symbol_or_metric' => 'USD_IDR_PROXY'],
                                [
                                    'value' => $currentVal,
                                    'change_abs' => $currentVal - $pastVal,
                                    'change_pct' => $pctChange,
                                    'source' => 'FRED',
                                    'last_sync' => now()
                                ]
                            );
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("[DataSyncService] FRED API Error: " . $e->getMessage());
            }
        }

        foreach ($macros as $key => $data) {
            MarketSnapshot::updateOrCreate(
                ['date' => $date, 'symbol_or_metric' => $key],
                [
                    'value' => $data['val'],
                    'change_abs' => $data['chg'],
                    'change_pct' => 0, // not applicable for all
                    'source' => 'FRED (Mock)',
                    'last_sync' => now()
                ]
            );
        }
    }
}
