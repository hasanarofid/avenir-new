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
     * Sinkronisasi dari Sectors API (mock/simulated for MVP)
     */
    protected function syncSectorsApp(string $date)
    {
        $apiKey = env('SECTOR_API_KEY');
        // Simulate fetching daily index data from sectors
        // In reality, this would hit https://api.sectors.app/v1/daily/{index}
        
        $indexes = ['IHSG', 'LQ45', 'IDX30'];
        foreach ($indexes as $idx) {
            MarketSnapshot::updateOrCreate(
                ['date' => $date, 'symbol_or_metric' => $idx],
                [
                    'value' => 7000 + rand(-100, 200),
                    'change_abs' => rand(-50, 50),
                    'change_pct' => rand(-100, 100) / 100,
                    'source' => 'Sectors.app',
                    'last_sync' => now()
                ]
            );
        }

        // Simulate fetching sector performances
        $sectors = ['Banking', 'Telecom', 'Consumer Staples', 'Healthcare', 'Energy', 'Transportation', 'Property', 'Infrastructure', 'Industrial', 'Retail', 'Basic Materials', 'Technology', 'Consumer Discretionary', 'Mining'];
        foreach ($sectors as $sector) {
            SectorBiasDaily::updateOrCreate(
                ['date' => $date, 'sector' => $sector],
                [
                    'bias' => 'neutral',
                    'return_1d' => rand(-300, 300) / 100,
                    'flow_value' => rand(-100000, 100000), // in millions
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
