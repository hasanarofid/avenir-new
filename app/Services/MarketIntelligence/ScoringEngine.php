<?php

namespace App\Services\MarketIntelligence;

use App\Models\MarketStanceDaily;
use App\Models\SectorBiasDaily;
use App\Models\DeskBrief;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ScoringEngine
{
    public function gatherMarketData(string $date, array $driversData = []): array
    {
        $latestDate = \App\Models\MarketSnapshot::whereDate('date', '<=', $date)
            ->where('symbol_or_metric', 'IHSG')
            ->max('date');
        $snapshots = $latestDate ? \App\Models\MarketSnapshot::whereDate('date', $latestDate)->get()->keyBy('symbol_or_metric') : collect();

        $ihsg = $snapshots->get('IHSG');
        
        if (!$ihsg) {
            throw new \Exception("Data IHSG belum ditarik dari API (Mungkin token habis atau data belum rilis untuk tanggal {$date}).");
        }

        $marketData = [
            'close' => (float) $ihsg->value,
            'ret_5d' => null, // will be calculated below
            'ihsg_return_1d' => (float) $ihsg->change_pct,
            'ma20' => null,
            'ma60' => null,
            'ret_20d' => null,
            'drawdown_20d' => null,
            'advancers' => 0,
            'decliners' => 0,
            'pct_above_ma20' => 0,
            'sector_positive_ratio' => 0,
            'new_high' => 0,
            'new_low' => 0,
            'foreign_net_5d' => 0,
            'institutional_net_5d' => 0,
            'positive_flow_days_5d' => 0,
            'total_market_value_5d' => 0,
            'positive_sectors' => 0,
            'total_sectors' => 0,
            'cyclical_positive_ratio' => 0,
            'leadership_concentration' => 0,
            'leadership_consistency_days' => 0,
            'volatility_percentile' => 0,
            'value_traded' => 0,
            'avg_value_20d' => 0,
            'daily_range_pct' => 0,
        ];

        $prices = [];
        if (!empty($ihsg->sparkline_json) && is_array($ihsg->sparkline_json)) {
            $prices = $ihsg->sparkline_json;
        } else {
            // Fallback: get previous day's sparkline and append today's close
            $prevIhsg = \App\Models\MarketSnapshot::where('symbol_or_metric', 'IHSG')
                ->whereNotNull('sparkline_json')
                ->whereDate('date', '<', $latestDate ?: $date)
                ->orderBy('date', 'desc')
                ->first();
                
            if ($prevIhsg && !empty($prevIhsg->sparkline_json) && is_array($prevIhsg->sparkline_json)) {
                $prices = $prevIhsg->sparkline_json;
                $prices[] = (float) $ihsg->value;
            }
        }

        if (count($prices) > 0) {
            $count = count($prices);
            
            if ($count > 0) {
                // ma20 uses 20 items
                $period20 = min(20, $count);
                $last20 = array_slice($prices, -$period20);
                $marketData['ma20'] = array_sum($last20) / $period20;
                
                // drawdown uses 20 items
                $lastVal = end($last20);
                $max20 = max($last20);
                $marketData['drawdown_20d'] = $max20 > 0 ? ($lastVal - $max20) / $max20 : 0;
                
                // ret_20d needs 21 items (today vs 20 days ago)
                $period21 = min(21, $count);
                $last21 = array_slice($prices, -$period21);
                $first21 = $last21[0];
                $marketData['ret_20d'] = $first21 > 0 ? ($lastVal - $first21) / $first21 : 0;
                
                // ret_5d needs 6 items (today vs 5 days ago)
                $period6 = min(6, $count);
                $last6 = array_slice($prices, -$period6);
                $first6 = $last6[0];
                $marketData['ret_5d'] = $first6 > 0 ? ($lastVal - $first6) / $first6 : 0;
            }
            
            if ($count >= 60) {
                $last60 = array_slice($prices, -60);
                $marketData['ma60'] = array_sum($last60) / 60;
            } elseif ($count > 0) {
                $marketData['ma60'] = array_sum($prices) / $count;
            }
        }
        
        // Fetch all relevant snapshots up to date
        $allSnapshots = \App\Models\MarketSnapshot::whereDate('date', '<=', $date)
            ->whereIn('symbol_or_metric', [
                'IHSG', 'FOREIGN_NET_TODAY', 'VALUE_TRADED_BN_IDR', 'USD_IDR_PROXY',
                'ADVANCERS', 'DECLINERS', 'STABLE'
            ])
            ->orderBy('date', 'desc')
            ->get();

        $latestSnapshots = $allSnapshots->groupBy('symbol_or_metric')->map(fn($g) => $g->first());

        $latestDate = $latestSnapshots->get('IHSG')->date ?? $date;

        // Populate basic market data from latest snapshots
        $marketData['advancers'] = (int) ($latestSnapshots->get('ADVANCERS')->value ?? 0);
        $marketData['decliners'] = (int) ($latestSnapshots->get('DECLINERS')->value ?? 0);
        $marketData['stable']    = (int) ($latestSnapshots->get('STABLE')->value ?? 0);
        $marketData['value_traded'] = (float) ($latestSnapshots->get('VALUE_TRADED_BN_IDR')->value ?? 0);
        
        // Group by date for Flow history (up to 20 days)
        $dates = $allSnapshots->whereIn('symbol_or_metric', ['FOREIGN_NET_TODAY', 'VALUE_TRADED_BN_IDR'])
            ->pluck('date')->unique()->values()->take(20);
            
        $flowHistory = [];
        foreach ($dates as $d) {
            $daySnaps = $allSnapshots->where('date', $d);
            $flowHistory[] = [
                'date' => $d,
                'foreign_net' => (float) ($daySnaps->firstWhere('symbol_or_metric', 'FOREIGN_NET_TODAY')->value ?? 0),
                'market_value' => (float) ($daySnaps->firstWhere('symbol_or_metric', 'VALUE_TRADED_BN_IDR')->value ?? 0),
            ];
        }
        $marketData['flow_history'] = $flowHistory;

        $flowDriver = collect($driversData)->firstWhere('rank', 2);
        if ($flowDriver && !empty($flowDriver['components']['data_quality']) && $flowDriver['components']['data_quality'] === 'live_flow') {
            $marketData['foreign_net_5d'] = (float) ($flowDriver['components']['foreign_net_5d'] ?? 0);
            $marketData['institutional_net_5d'] = (float) ($flowDriver['components']['institutional_net_5d'] ?? 0);
            $marketData['positive_flow_days_5d'] = (int) ($flowDriver['components']['positive_flow_days_5d'] ?? 0);
            $marketData['total_market_value_5d'] = (float) ($flowDriver['components']['market_gross_5d'] ?? 0);
        }

        $breadthDriver = collect($driversData)->firstWhere('rank', 3);
        if ($breadthDriver && !empty($breadthDriver['components']['data_quality']) && $breadthDriver['components']['data_quality'] === 'live_sectors') {
            // Do not override advancers/decliners if they come from PDF
            if ($marketData['advancers'] === 0) {
                $marketData['advancers'] = (int) ($breadthDriver['components']['advancers'] ?? 0);
                $marketData['decliners'] = (int) ($breadthDriver['components']['decliners'] ?? 0);
            }
        }

        $sectorDriver = collect($driversData)->firstWhere('rank', 4);
        if ($sectorDriver && !empty($sectorDriver['components']['data_quality']) && $sectorDriver['components']['data_quality'] === 'live_sectors') {
            $marketData['positive_sectors'] = (int) ($sectorDriver['components']['positive_sectors'] ?? 0);
            $marketData['total_sectors'] = (int) ($sectorDriver['components']['total_sectors'] ?? 0);
            $marketData['sector_positive_ratio'] = (float) ($sectorDriver['components']['sector_positive_ratio'] ?? 0);
            $marketData['leadership_concentration'] = (float) ($sectorDriver['components']['leadership_concentration'] ?? 0);
        }

        // If sector stats are empty (due to API limit), calculate them from SectorBiasDaily which was populated by PDF
        if ($marketData['total_sectors'] === 0) {
            $sectors = \App\Models\SectorBiasDaily::whereDate('date', $date)->get();
            $totalSectors = $sectors->count();
            if ($totalSectors > 0) {
                $positiveSectors = $sectors->where('return_1d', '>', 0)->count();
                $marketData['total_sectors'] = $totalSectors;
                $marketData['positive_sectors'] = $positiveSectors;
                $marketData['sector_positive_ratio'] = $positiveSectors / $totalSectors;
                
                // Cyclical positive ratio estimation based on IDX Cyclical & Financial sectors
                $cyclicals = ['Financials', 'Consumer Cyclicals', 'Basic Materials', 'Industrials', 'Energy'];
                $cyclicalSectors = $sectors->whereIn('sector', $cyclicals);
                if ($cyclicalSectors->count() > 0) {
                    $positiveCyclicals = $cyclicalSectors->where('return_1d', '>', 0)->count();
                    $marketData['cyclical_positive_ratio'] = $positiveCyclicals / $cyclicalSectors->count();
                } else {
                    $marketData['cyclical_positive_ratio'] = 0;
                }

                // Leadership concentration (how many sectors drive most of the positive returns)
                // Roughly estimate: standard deviation of returns
                $returns = $sectors->pluck('return_1d')->map(fn($v) => (float)$v)->toArray();
                if (count($returns) > 1) {
                    $mean = array_sum($returns) / count($returns);
                    $variance = 0;
                    foreach ($returns as $r) {
                        $variance += pow($r - $mean, 2);
                    }
                    $variance /= count($returns);
                    $stdDev = sqrt($variance);
                    $marketData['leadership_concentration'] = $stdDev / 10; // Normalize roughly
                }
            }
        }
        
        return [
            'marketData' => $marketData,
            'latestDate' => $latestDate
        ];
    }

    /**
     * Hitung Regime Score berdasarkan bobot PRD.
     * regime = 0.25*flow + 0.20*breadth + 0.15*momentum + 0.15*rupiah + 0.15*yield + 0.10*rotasi
     */
    public function calculateRegimeScore(string $date, array $driversData = []): MarketStanceDaily
    {
        $gathered = $this->gatherMarketData($date, $driversData);
        $result = $this->calculateMarketRegime($gathered['marketData']);
        $scores = $result['component_scores'];

        $stance = MarketStanceDaily::updateOrCreate(
            ['date' => $date],
            [
                'score' => round($result['final_score']),
                'label' => str_replace('_', ' ', $result['regime']),
                'foreign_score' => $scores['flow'],
                'breadth_score' => $scores['breadth'],
                'momentum_score' => $scores['price_trend'],
                'rupiah_score' => $scores['volatility_liquidity'],
                'yield_score' => 0, // Unused but kept for DB compatibility
                'sector_score' => $scores['sector_rotation'],
            ]
        );

        return $stance;
    }

    /**
     * Hitung Confluence Score untuk setiap sektor di hari tersebut.
     * total = Σ (-4...+4)
     * label = ≥+3 Strong, +2 Building, +1 Watch, mayoritas negatif Avoid
     */
    public function calculateConfluence(string $date)
    {
        $sectors = SectorBiasDaily::whereDate('date', $date)->get();

        foreach ($sectors as $sector) {
            // Scores are between -1, 0, 1
            $total = $sector->rotation_score + $sector->smart_money_score + $sector->valuation_score + $sector->event_score;
            
            $label = 'Avoid';
            if ($total >= 3) {
                $label = 'Strong';
            } elseif ($total == 2) {
                $label = 'Building';
            } elseif ($total == 1) {
                $label = 'Watch';
            }

            $sector->update([
                'confluence_score' => $total,
                'confluence_label' => $label
            ]);
        }
    }

    protected function clamp(float $value, float $minValue = 0, float $maxValue = 100): int
    {
        return (int) max($minValue, min($value, $maxValue));
    }

    protected function scorePiecewise(?float $value, array $points): ?float
    {
        if ($value === null || is_nan($value) || is_infinite($value)) {
            return null;
        }

        // Sort points by X
        usort($points, function($a, $b) {
            return $a[0] <=> $b[0];
        });

        if ($value <= $points[0][0]) return $points[0][1];
        
        $lastIdx = count($points) - 1;
        if ($value >= $points[$lastIdx][0]) return $points[$lastIdx][1];

        for ($i = 0; $i < $lastIdx; $i++) {
            $x0 = $points[$i][0];
            $y0 = $points[$i][1];
            $x1 = $points[$i+1][0];
            $y1 = $points[$i+1][1];

            if ($value >= $x0 && $value <= $x1) {
                if ($x1 == $x0) return $y0; // prevent div by zero
                $ratio = ($value - $x0) / ($x1 - $x0);
                return $y0 + $ratio * ($y1 - $y0);
            }
        }

        return null;
    }

    public function calculatePriceTrendScore(array $data): int
    {
        $close = $data['close'] ?? null;
        $ma20 = $data['ma20'] ?? null;
        $ma60 = $data['ma60'] ?? null;
        $ret_5d = $data['ret_5d'] ?? null;
        $ret_20d = $data['ret_20d'] ?? null;
        $drawdown_20d = $data['drawdown_20d'] ?? null;

        // Note: For ret_5d, since gatherMarketData didn't fetch it, we should calculate it from sparkline_json if possible.
        // Actually, let's try to calculate it if we have ma20 and close. But wait, ret_5d is just the return over 5 days.
        // gatherMarketData currently sets ret_5d to 1-day return incorrectly!
        // We will fix gatherMarketData separately. Here we just use the provided values.

        $c_ma20 = ($close !== null && $ma20 !== null && $ma20 > 0) ? ($close / $ma20) - 1 : null;
        $m_ma60 = ($ma20 !== null && $ma60 !== null && $ma60 > 0) ? ($ma20 / $ma60) - 1 : null;

        $scores = [
            'close_vs_ma20' => $this->scorePiecewise($c_ma20, [
                [-0.15, 5], [-0.10, 12], [-0.07, 22], [-0.03, 38],
                [0.00, 60], [0.02, 75], [0.05, 90], [0.08, 100],
            ]),
            'ma20_vs_ma60' => $this->scorePiecewise($m_ma60, [
                [-0.15, 5], [-0.10, 15], [-0.07, 25], [-0.03, 40],
                [0.00, 60], [0.02, 75], [0.05, 90], [0.08, 100],
            ]),
            'return_5d' => $this->scorePiecewise($ret_5d, [
                [-0.12, 5], [-0.08, 12], [-0.05, 25], [-0.02, 40],
                [0.00, 52], [0.02, 65], [0.05, 85], [0.08, 100],
            ]),
            'return_20d' => $this->scorePiecewise($ret_20d, [
                [-0.20, 5], [-0.12, 15], [-0.08, 25], [-0.05, 35],
                [0.00, 52], [0.03, 65], [0.08, 85], [0.12, 100],
            ]),
            'drawdown_20d' => $this->scorePiecewise($drawdown_20d, [
                [-0.25, 5], [-0.18, 15], [-0.12, 30], [-0.08, 45],
                [-0.05, 60], [-0.03, 75], [-0.01, 90], [0.00, 100],
            ])
        ];

        $weights = [
            'close_vs_ma20' => 0.30,
            'ma20_vs_ma60' => 0.25,
            'return_5d' => 0.20,
            'return_20d' => 0.15,
            'drawdown_20d' => 0.10,
        ];

        $valid_score = 0;
        $valid_weight = 0;

        foreach ($scores as $key => $s) {
            if ($s !== null) {
                $valid_score += $s * $weights[$key];
                $valid_weight += $weights[$key];
            }
        }

        if ($valid_weight == 0) return 0;

        return (int) round($this->clamp($valid_score / $valid_weight));
    }

    public function calculateBreadthScore(array $data): int
    {
        $advancers = $data['advancers'] ?? 0;
        $decliners = $data['decliners'] ?? 0;
        $stable = $data['stable'] ?? 0;

        $total = $advancers + $decliners + $stable;
        if ($total > 0) {
            $score = ($advancers / $total) * 100;
        } else {
            $score = 0;
        }
        
        return $this->clamp((int)round($score));
    }

    public function calculateFlowScore(array $data): int
    {
        $history = $data['flow_history'] ?? [];
        if (empty($history)) {
            return 50;
        }

        // Calculate sums and averages
        $foreign_net_1d = $history[0]['foreign_net'] ?? 0;
        $market_value_1d = $history[0]['market_value'] ?? 0;

        $foreign_net_5d = 0;
        $total_market_value_5d = 0;
        $positive_days_5d = 0;
        $count_5d = 0;
        
        $targetDate = \Carbon\Carbon::parse($data['latestDate'] ?? today());

        foreach ($history as $h) {
            $hDate = \Carbon\Carbon::parse($h['date']);
            if ($targetDate->diffInDays($hDate, true) <= 7 && $count_5d < 5) {
                $foreign_net_5d += $h['foreign_net'];
                $total_market_value_5d += $h['market_value'];
                if ($h['foreign_net'] > 0) $positive_days_5d++;
                $count_5d++;
            }
        }

        $foreign_net_20d = 0;
        $total_market_value_20d = 0;
        $count_20d = 0;
        foreach ($history as $h) {
            $hDate = \Carbon\Carbon::parse($h['date']);
            if ($targetDate->diffInDays($hDate, true) <= 30 && $count_20d < 20) {
                $foreign_net_20d += $h['foreign_net'];
                $total_market_value_20d += $h['market_value'];
                $count_20d++;
            }
        }
        $avg_market_value_20d = $count_20d > 0 ? $total_market_value_20d / $count_20d : 0;

        // Intensity
        $foreign_intensity_1d = $market_value_1d > 0 ? $foreign_net_1d / $market_value_1d : 0;
        $foreign_intensity_5d = $total_market_value_5d > 0 ? $foreign_net_5d / $total_market_value_5d : 0;
        $foreign_intensity_20d = $total_market_value_20d > 0 ? $foreign_net_20d / $total_market_value_20d : 0;

        // Liquidity ratio
        $normal_5d_value = $avg_market_value_20d * 5;
        $liquidity_ratio = $normal_5d_value > 0 ? $total_market_value_5d / $normal_5d_value : 0;

        // Scoring functions
        $scorePiecewise = function($val, $points) {
            if ($val <= $points[0][0]) return $points[0][1];
            $last = end($points);
            if ($val >= $last[0]) return $last[1];
            for ($i = 0; $i < count($points) - 1; $i++) {
                if ($val >= $points[$i][0] && $val <= $points[$i+1][0]) {
                    $ratio = ($val - $points[$i][0]) / ($points[$i+1][0] - $points[$i][0]);
                    return $points[$i][1] + $ratio * ($points[$i+1][1] - $points[$i][1]);
                }
            }
            return 50;
        };

        $scoreIntensity = function($intensity) use ($scorePiecewise) {
            return $scorePiecewise($intensity, [
                [-0.050, 5], [-0.030, 15], [-0.015, 30], [-0.005, 42],
                [0.000, 50], [0.005, 60], [0.015, 75], [0.030, 90], [0.050, 100],
            ]);
        };

        $scoreConsistency = function($days) {
            if ($days >= 5) return 100;
            if ($days == 4) return 85;
            if ($days == 3) return 65;
            if ($days == 2) return 45;
            if ($days == 1) return 25;
            return 10;
        };

        $scoreLiquidity = function($ratio) use ($scorePiecewise) {
            return $scorePiecewise($ratio, [
                [0.30, 15], [0.50, 30], [0.70, 45], [0.90, 60],
                [1.00, 70], [1.20, 85], [1.50, 100],
            ]);
        };

        $trend_score = (0.20 * $scoreIntensity($foreign_intensity_1d)) + 
                       (0.50 * $scoreIntensity($foreign_intensity_5d)) + 
                       (0.30 * $scoreIntensity($foreign_intensity_20d));
                       
        $consistency_score = $scoreConsistency($positive_days_5d);
        $liquidity_score = $scoreLiquidity($liquidity_ratio);

        $final_score = ($trend_score * 0.40) + ($consistency_score * 0.25) + ($liquidity_score * 0.25) /* broker_pulse is omitted, total weight 0.90 */;
        $final_score = $final_score / 0.90;

        return $this->clamp((int)round($final_score));
    }

    public function calculateSectorRotationScore(array $data): int
    {
        $positiveSectors = $data['positive_sectors'] ?? 0;
        $totalSectors = $data['total_sectors'] ?? 0;

        if ($totalSectors == 0) return 50;

        $score = ($positiveSectors / $totalSectors) * 100;

        return $this->clamp((int)round($score));
    }

    public function calculateVolatilityLiquidityScore(array $data): int
    {
        $volatilityPercentile = $data['volatility_percentile'] ?? 0;
        $valueTraded = $data['value_traded'] ?? 0;
        $avgValue20d = $data['avg_value_20d'] ?? 0;
        $ihsgReturn1d = $data['ihsg_return_1d'] ?? 0;

        $score = 0;
        
        // Volatilitas normal, tidak terlalu mati dan tidak terlalu panik
        if ($volatilityPercentile >= 0.25 && $volatilityPercentile <= 0.75) {
            $score += 30;
        }

        // Value transaksi di atas rata-rata dan IHSG naik
        if ($valueTraded > $avgValue20d && $ihsgReturn1d > 0) {
            $score += 40;
        } elseif ($valueTraded >= $avgValue20d * 0.8) {
            $score += 30;
        }

        return $this->clamp($score);
    }

    public function calculateFinalRegimeScore(array $scores): float
    {
        $finalScore = (
            ($scores['price_trend'] ?? 0) * 0.30 +
            ($scores['breadth'] ?? 0) * 0.25 +
            ($scores['flow'] ?? 0) * 0.20 +
            ($scores['sector_rotation'] ?? 0) * 0.15 +
            ($scores['volatility_liquidity'] ?? 0) * 0.10
        );

        return round($finalScore, 2);
    }

    public function classifyMarketRegime(array $scores, float $finalScore): string
    {
        $price = $scores['price_trend'] ?? 0;
        $breadth = $scores['breadth'] ?? 0;
        $flow = $scores['flow'] ?? 0;
        $sector = $scores['sector_rotation'] ?? 0;
        $volatility = $scores['volatility_liquidity'] ?? 0;

        // Trend, breadth, dan flow sama-sama kuat
        if ($price >= 70 && $breadth >= 60 && $flow >= 60) {
            return "RISK_ON_CONFIRMATION";
        }

        // Harga belum kuat, tapi flow mulai masuk
        if ($price <= 50 && $flow >= 60 && $volatility >= 50) {
            return "QUIET_ACCUMULATION";
        }

        // IHSG terlihat kuat, tapi breadth dan flow lemah
        if ($price >= 60 && $breadth < 50 && $flow < 50) {
            return "VULNERABLE_RALLY";
        }
        
        if ($price < 40 && $flow < 40) {
            return "DISTRIBUTION";
        }

        return "NEUTRAL_ROTATION";
    }

    public function calculateMarketRegime(array $data): array
    {
        $priceScore = $this->calculatePriceTrendScore($data);
        $breadth = $this->calculateBreadthScore($data);
        $flow = $this->calculateFlowScore($data);
        $sector = $this->calculateSectorRotationScore($data);
        $volatility = $this->calculateVolatilityLiquidityScore($data);

        $scores = [
            'price_trend' => $priceScore,
            'breadth' => $breadth,
            'flow' => $flow,
            'sector_rotation' => $sector,
            'volatility_liquidity' => $volatility,
        ];

        $finalScore = $this->calculateFinalRegimeScore($scores);
        $regime = $this->classifyMarketRegime($scores, $finalScore);

        return [
            'final_score' => $finalScore,
            'regime' => $regime,
            'component_scores' => $scores,
        ];
    }
}
