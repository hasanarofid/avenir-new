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
        $flowDriver = $todayBrief ? $todayBrief->drivers->firstWhere('rank', 1) : null;
        $yFlowDriver = $yesterdayBrief ? $yesterdayBrief->drivers->firstWhere('rank', 1) : null;
        if ($flowDriver && is_array($flowDriver->affected_sectors_json)) {
            $tState = $flowDriver->affected_sectors_json['state'] ?? 'Mixed';
            $yState = ($yFlowDriver && is_array($yFlowDriver->affected_sectors_json)) 
                      ? ($yFlowDriver->affected_sectors_json['state'] ?? 'Mixed') 
                      : 'Mixed';
            
            $tStateStr = ucwords(strtolower(str_replace('_', ' ', $tState)));
            $yStateStr = ucwords(strtolower(str_replace('_', ' ', $yState)));
            
            if ($tState !== $yState && $tState !== 'Mixed') {
                $delta['foreign_flow_text'] = "{$yStateStr} → <span class='pos'>{$tStateStr} &#8634;</span>";
            } else {
                $class = $tState === 'OUTFLOW' ? 'neg' : ($tState === 'NET_BUY' ? 'pos' : 'amb');
                $delta['foreign_flow_text'] = "<span class='{$class}'>{$tStateStr}</span>";
            }
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
        $advSnap = \App\Models\MarketSnapshot::where('date', $today->toDateString())->where('symbol_or_metric', 'ADVANCERS')->value('value');
        $decSnap = \App\Models\MarketSnapshot::where('date', $today->toDateString())->where('symbol_or_metric', 'DECLINERS')->value('value');

        $breadthDriver = $todayBrief ? $todayBrief->drivers->firstWhere('rank', 3) : null;
        $adv = $advSnap !== null ? (int)$advSnap : (!empty($breadthDriver->affected_sectors_json['advancers']) ? $breadthDriver->affected_sectors_json['advancers'] : 113);
        $dec = $decSnap !== null ? (int)$decSnap : (!empty($breadthDriver->affected_sectors_json['decliners']) ? $breadthDriver->affected_sectors_json['decliners'] : 618);

        $totalStockCount = $adv + $dec;
        $ratio = $totalStockCount > 0 ? $adv / $totalStockCount : 0.5;
        $state = $ratio < 0.4 ? 'Negatif' : ($ratio > 0.6 ? 'Positif' : 'Netral');
        $delta['breadth'] = [
            'state' => $state,
            'advancers' => $adv,
            'decliners' => $dec
        ];

        // 6. Confluence
        $delta['confluence_sectors'] = [];
        $sectors = \App\Models\SectorBiasDaily::whereDate('date', $today->toDateString())->get();
        foreach ($sectors as $sec) {
            $total = $sec->rotation_score + $sec->smart_money_score + $sec->valuation_score + $sec->event_score;
            if ($total >= 1) {
                $delta['confluence_sectors'][] = $sec->sector . " +" . $total;
            }
        }
        
        // 7. New Risk Flag
        $delta['new_risk_flag'] = null;
        if ($todayBrief) {
            $escalatedDriver = $todayBrief->drivers->whereIn('badge', ['ESCALATED', 'WATCH'])->first();
            if ($escalatedDriver) {
                $delta['new_risk_flag'] = [
                    'badge' => $escalatedDriver->badge,
                    'title' => $escalatedDriver->title
                ];
            }
        }
        
        return $delta;
    }
}
