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
            'ma20' => 0,
            'ma60' => 0,
            'ret_20d' => 0,
            'drawdown_20d' => 0,
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

        if (!empty($ihsg->sparkline_json) && is_array($ihsg->sparkline_json)) {
            $prices = $ihsg->sparkline_json;
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

        $marketData['advancers'] = (int) ($snapshots->get('ADVANCERS')->value ?? 0);
        $marketData['decliners'] = (int) ($snapshots->get('DECLINERS')->value ?? 0);
        $marketData['stable'] = (int) ($snapshots->get('STABLE')->value ?? 0);
        $marketData['value_traded'] = (float) ($snapshots->get('VALUE_TRADED_BN_IDR')->value ?? 0);
        $marketData['foreign_net_5d'] = (float) ($snapshots->get('FOREIGN_NET_TODAY')->value ?? 0);

        $flowDriver = collect($driversData)->firstWhere('rank', 1);
        if ($flowDriver && !empty($flowDriver['components']['data_quality']) && $flowDriver['components']['data_quality'] === 'live_sectors') {
            // Only override if we have live data from API, but since API is out, we rely on PDF above
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
        $foreignNet = $data['foreign_net_5d'] ?? 0;
        $institutionalNet = $data['institutional_net_5d'] ?? 0;
        $positiveFlowDays = $data['positive_flow_days_5d'] ?? 0;
        $totalMarketValue = $data['total_market_value_5d'] ?? 0;

        $totalNetFlow = $foreignNet + $institutionalNet;
        $flowIntensity = $totalMarketValue > 0 ? ($totalNetFlow / $totalMarketValue) : 0;

        $score = 0;
        if ($foreignNet > 0) $score += 35;
        if ($institutionalNet > 0) $score += 25;
        if ($positiveFlowDays >= 3) $score += 20;
        if ($flowIntensity > 0) $score += 20;

        return $this->clamp($score);
    }

    public function calculateSectorRotationScore(array $data): int
    {
        $positiveSectors = $data['positive_sectors'] ?? 0;
        $totalSectors = $data['total_sectors'] ?? 0;
        $cyclicalPositiveRatio = $data['cyclical_positive_ratio'] ?? 0;
        $leadershipConcentration = $data['leadership_concentration'] ?? 0;
        $leadershipConsistency = $data['leadership_consistency_days'] ?? 0;

        $sectorRatio = $totalSectors > 0 ? $positiveSectors / $totalSectors : 0;
        
        $score = 0;
        if ($sectorRatio > 0.55) {
            $score += 35;
        }

        if ($cyclicalPositiveRatio > 0.5) {
            $score += 30;
        }

        if ($leadershipConcentration < 0.4) {
            $score += 20; 
        }

        if ($leadershipConsistency >= 2) {
            $score += 15;
        }

        return $this->clamp($score);
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
