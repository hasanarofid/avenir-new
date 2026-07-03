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
        
        // Load today's and yesterday's briefs for drivers
        $todayBrief = DeskBrief::with('drivers')->whereDate('date', $today->toDateString())->first();
        $yesterdayBrief = DeskBrief::with('drivers')->whereDate('date', '<', $today->toDateString())->orderBy('date', 'desc')->first();
        
        // 3. Foreign Flow
        // Fetch from Driver 1 (IDX_LIQUIDITY_FOREIGN_FLOW)
        $flowDriver = $todayBrief ? $todayBrief->drivers->firstWhere('rank', 1) : null;
        $delta['foreign_flow_state'] = 'Mixed';
        if ($flowDriver && is_array($flowDriver->affected_sectors_json)) {
            $delta['foreign_flow_state'] = $flowDriver->affected_sectors_json['state'] ?? 'Mixed';
            $delta['foreign_flow_badge'] = $flowDriver->explanation; // e.g. "▲ FLIPPED +" or "▲ NET BUY"
        }

        // 4. Driver Escalated
        $delta['driver_escalated'] = null;
        if ($todayBrief && $yesterdayBrief) {
            foreach ($todayBrief->drivers as $tDriver) {
                $yDriver = $yesterdayBrief->drivers->firstWhere('rank', $tDriver->rank);
                if ($yDriver && $tDriver->impact_level === 'HIGH' && $yDriver->impact_level !== 'HIGH') {
                    $delta['driver_escalated'] = [
                        'title' => $tDriver->title,
                        'from' => ucfirst(strtolower($yDriver->impact_level)),
                        'to' => 'High'
                    ];
                    break;
                }
            }
        }

        // 5. Breadth
        $breadthDriver = $todayBrief ? $todayBrief->drivers->firstWhere('rank', 3) : null;
        if ($breadthDriver && is_array($breadthDriver->affected_sectors_json)) {
            $adv = $breadthDriver->affected_sectors_json['advancers'] ?? 0;
            $dec = $breadthDriver->affected_sectors_json['decliners'] ?? 0;
            $ratio = $breadthDriver->affected_sectors_json['advance_ratio'] ?? 0;
            $state = $ratio < 0.4 ? 'Negatif' : ($ratio > 0.6 ? 'Positif' : 'Netral');
            $delta['breadth'] = [
                'state' => $state,
                'advancers' => $adv,
                'decliners' => $dec
            ];
        }

        // 6. Confluence
        $delta['confluence_sectors'] = [];
        $sectors = \App\Models\SectorBiasDaily::whereDate('date', $today->toDateString())->get();
        foreach ($sectors as $sec) {
            $total = $sec->rotation_score + $sec->smart_money_score + $sec->valuation_score + $sec->event_score;
            if ($total >= 1) {
                $delta['confluence_sectors'][] = $sec->sector . " +" . $total;
            }
        }
        
        return $delta;
    }
}
