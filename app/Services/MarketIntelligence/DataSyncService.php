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
            SectorBiasDaily::updateOrCreate(
                ['date' => $secData['date'], 'sector' => $secData['sector']],
                [
                    'return_1d' => $secData['return_1d'],
                    // For now, keep others as neutral/0 until we have specific data sources for them
                    'bias' => 'neutral',
                    'flow_value' => rand(-50000, 50000), // Still mocked for now as it's not in the index API
                    'rotation_score' => rand(-1, 1),
                    'smart_money_score' => rand(-1, 1),
                    'valuation_score' => rand(-1, 1),
                    'event_score' => rand(-1, 1),
                ]
            );
        }
    }

    /**
     * Sinkronisasi dari FRED/Makro (mock/simulated for MVP since no API key provided)
     */
    protected function syncMacroData(string $date)
    {
        $macros = [
            'GLOBAL_GROWTH' => ['val' => 3.1, 'chg' => 0],
            'US_INFLATION' => ['val' => 2.3, 'chg' => -0.1],
            'G3_LIQUIDITY' => ['val' => 6.1, 'chg' => 0.2]
        ];

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
