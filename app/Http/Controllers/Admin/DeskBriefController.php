<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeskBrief;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeskBriefController extends Controller
{
    public function index()
    {
        $deskBriefs = DeskBrief::with(['analyst', 'marketStance'])->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);
            
        return Inertia::render('Admin/DeskBrief/Index', [
            'deskBriefs' => $deskBriefs
        ]);
    }

    public function edit($id)
    {
        $deskBrief = DeskBrief::with('marketStance')->findOrFail($id);
        
        return Inertia::render('Admin/DeskBrief/Edit', [
            'deskBrief' => $deskBrief
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'market_read' => 'nullable|string',
            'so_what' => 'nullable|string',
            'what_to_do' => 'nullable|string',
            'momentum_score' => 'nullable|numeric|min:0|max:100',
            'breadth_score' => 'nullable|numeric|min:0|max:100',
            'foreign_score' => 'nullable|numeric|min:0|max:100',
            'sector_score' => 'nullable|numeric|min:0|max:100',
            'rupiah_score' => 'nullable|numeric|min:0|max:100',
        ]);

        $deskBrief = DeskBrief::findOrFail($id);
        
        $deskBrief->update([
            'title' => $request->title,
            'market_read' => $request->market_read,
            'so_what' => $request->so_what,
            'what_to_do' => $request->what_to_do,
        ]);

        if ($deskBrief->market_stance_id && $request->has('momentum_score')) {
            $stance = \App\Models\MarketStanceDaily::find($deskBrief->market_stance_id);
            if ($stance) {
                $stance->momentum_score = $request->momentum_score;
                $stance->breadth_score = $request->breadth_score;
                $stance->foreign_score = $request->foreign_score;
                $stance->sector_score = $request->sector_score;
                $stance->rupiah_score = $request->rupiah_score;
                
                // Recalculate Total Score
                $totalScore = ($request->momentum_score * 0.3) +
                              ($request->breadth_score * 0.25) +
                              ($request->foreign_score * 0.2) +
                              ($request->sector_score * 0.15) +
                              ($request->rupiah_score * 0.1);
                              
                $stance->score = round($totalScore);
                $stance->save();
            }
        }

        return redirect()->route('admin.desk-brief.index')->with('success', 'Desk Brief updated successfully.');
    }

    public function publish($id)
    {
        $deskBrief = DeskBrief::findOrFail($id);
        
        $deskBrief->update([
            'status' => 'published',
            'published_at' => now(),
            'analyst_id' => auth()->id()
        ]);

        return redirect()->route('admin.desk-brief.index')->with('success', 'Desk Brief published successfully.');
    }

    public function runTester(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);
        
        $date = \Carbon\Carbon::parse($request->date)->toDateString();
        
        // 1. Calculate Key Drivers (to get flow & breadth components)
        $keyDriversEngine = app(\App\Services\MarketIntelligence\KeyDriversEngine::class);
        
        $usdIdrProxy = \App\Models\MarketSnapshot::where('date', '<=', $date)
            ->where('symbol_or_metric', 'USD_IDR_PROXY')
            ->orderBy('date', 'desc')
            ->first();

        $manualInputs = [
            'RUPIAH_BI_SBN_YIELD' => [
                'usd_idr_change_5d' => $usdIdrProxy ? (float) $usdIdrProxy->change_pct : null,
                'sbn_10y' => null,
                'sbn_10y_change_5d' => null,
                'bi_stance' => 'neutral',
            ]
        ];

        $driversData = $keyDriversEngine->buildIhsgKeyDrivers('LQ45', 5, $date, $manualInputs);
        
        // 2. Fetch the same logic as ScoringEngine->calculateRegimeScore to get raw data
        $latestDate = \App\Models\MarketSnapshot::whereDate('date', '<=', $date)->max('date');
        $snapshots = $latestDate ? \App\Models\MarketSnapshot::whereDate('date', $latestDate)->get()->keyBy('symbol_or_metric') : collect();
        $ihsg = $snapshots->get('IHSG');
        
        // Base Dummy Data (Same as ScoringEngine fallback)
        $marketData = [
            "close" => 7200, "ma20" => 7100, "ma60" => 7050,
            "ret_5d" => 0.012, "ret_20d" => 0.028, "drawdown_20d" => -0.015,
            "advancers" => 520, "decliners" => 159, "pct_above_ma20" => 0.58,
            "sector_positive_ratio" => 0.45, "new_high" => 12, "new_low" => 9,
            "foreign_net_5d" => 850000000000, "institutional_net_5d" => 300000000000,
            "positive_flow_days_5d" => 4, "total_market_value_5d" => 55000000000000,
            "positive_sectors" => 5, "total_sectors" => 11,
            "cyclical_positive_ratio" => 0.42, "leadership_concentration" => 0.55,
            "leadership_consistency_days" => 2, "volatility_percentile" => 0.55,
            "value_traded" => 12000000000000, "avg_value_20d" => 10000000000000,
            "daily_range_pct" => 0.012, "ihsg_return_1d" => 0.005,
        ];

        // Override dummy dengan real data
        if ($ihsg) {
            $marketData['close'] = (float) $ihsg->value;
            $marketData['ret_5d'] = (float) $ihsg->change_pct;
            $marketData['ihsg_return_1d'] = (float) $ihsg->change_pct;
            if (!empty($ihsg->sparkline_json) && is_array($ihsg->sparkline_json)) {
                $prices = $ihsg->sparkline_json;
                $count = count($prices);
                if ($count > 0) {
                    $period = min(20, $count);
                    $lastN = array_slice($prices, -$period);
                    $marketData['ma20'] = array_sum($lastN) / $period;
                    $firstOfN = $lastN[0];
                    $lastOfN = end($lastN);
                    $marketData['ret_20d'] = $firstOfN > 0 ? ($lastOfN - $firstOfN) / $firstOfN : 0;
                    $maxOfN = max($lastN);
                    $marketData['drawdown_20d'] = $maxOfN > 0 ? ($lastOfN - $maxOfN) / $maxOfN : 0;
                }
                if ($count >= 60) {
                    $last60 = array_slice($prices, -60);
                    $marketData['ma60'] = array_sum($last60) / 60;
                } elseif ($count > 0) {
                    $marketData['ma60'] = array_sum($prices) / $count;
                }
            }
        }

        $flowDriver = collect($driversData)->firstWhere('rank', 1);
        if ($flowDriver && !empty($flowDriver['components']['data_quality']) && $flowDriver['components']['data_quality'] === 'live_sectors') {
            $marketData['foreign_net_5d'] = (float) ($flowDriver['components']['foreign_net_5d'] ?? 0);
            $marketData['institutional_net_5d'] = (float) ($flowDriver['components']['institutional_net_5d'] ?? 0);
            $marketData['positive_flow_days_5d'] = (int) ($flowDriver['components']['positive_flow_days_5d'] ?? 0);
            $marketData['total_market_value_5d'] = (float) ($flowDriver['components']['market_gross_5d'] ?? 0);
        }

        $breadthDriver = collect($driversData)->firstWhere('rank', 3);
        if ($breadthDriver && !empty($breadthDriver['components']['data_quality']) && $breadthDriver['components']['data_quality'] === 'live_sectors') {
            $marketData['advancers'] = (int) ($breadthDriver['components']['advancers'] ?? $marketData['advancers']);
            $marketData['decliners'] = (int) ($breadthDriver['components']['decliners'] ?? $marketData['decliners']);
        }

        $sectorDriver = collect($driversData)->firstWhere('rank', 4);
        if ($sectorDriver && !empty($sectorDriver['components']['data_quality']) && $sectorDriver['components']['data_quality'] === 'live_sectors') {
            $marketData['positive_sectors'] = (int) ($sectorDriver['components']['positive_sectors'] ?? $marketData['positive_sectors']);
            $marketData['total_sectors'] = (int) ($sectorDriver['components']['total_sectors'] ?? $marketData['total_sectors']);
            $marketData['sector_positive_ratio'] = (float) ($sectorDriver['components']['sector_positive_ratio'] ?? $marketData['sector_positive_ratio']);
            $marketData['leadership_concentration'] = (float) ($sectorDriver['components']['leadership_concentration'] ?? $marketData['leadership_concentration']);
        }

        $scoringEngine = app(\App\Services\MarketIntelligence\ScoringEngine::class);
        $result = $scoringEngine->calculateMarketRegime($marketData);
        
        return response()->json([
            'date' => $date,
            'latest_available_date' => $latestDate,
            'raw_data' => $marketData,
            'scores' => $result['component_scores'],
            'final_score' => $result['final_score'],
            'regime' => $result['regime'],
        ]);
    }
}
