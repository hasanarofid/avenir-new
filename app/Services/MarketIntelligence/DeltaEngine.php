<?php

namespace App\Services\MarketIntelligence;

use App\Models\MarketStanceDaily;
use App\Models\DeskBrief;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DeltaEngine
{
    /**
     * Hitung Delta / "What Changed" antara hari ini dan hari bursa sebelumnya
     */
    public function getWhatChanged(string $date): array
    {
        $today = Carbon::parse($date);
        
        $todayStance = MarketStanceDaily::whereDate('date', $today->toDateString())->first();
        // Cari stance hari sebelumnya (asumsi kemarin)
        $yesterdayStance = MarketStanceDaily::whereDate('date', '<', $today->toDateString())
                            ->orderBy('date', 'desc')
                            ->first();

        if (!$todayStance || !$yesterdayStance) {
            return [];
        }

        $delta = [];
        
        // 1. Regime Score Delta
        $regimeDiff = $todayStance->score - $yesterdayStance->score;
        $delta['regime'] = $regimeDiff;
        $delta['regime_trend'] = $regimeDiff > 0 ? 'warming' : ($regimeDiff < 0 ? 'cooling' : 'neutral');
        
        // 2. Stance Label Change
        $delta['stance_changed'] = $todayStance->label !== $yesterdayStance->label;
        
        // 3. Foreign Flow Flip
        $delta['flow_flipped'] = ($todayStance->foreign_score >= 50 && $yesterdayStance->foreign_score < 50) || 
                                 ($todayStance->foreign_score < 50 && $yesterdayStance->foreign_score >= 50);

        // Simulated risks and breadth delta for now
        $delta['new_risk_flags'] = [];
        $delta['breadth_changed'] = false;
        
        return $delta;
    }
}
