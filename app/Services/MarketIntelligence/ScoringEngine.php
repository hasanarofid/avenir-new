<?php

namespace App\Services\MarketIntelligence;

use App\Models\MarketStanceDaily;
use App\Models\SectorBiasDaily;
use App\Models\DeskBrief;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ScoringEngine
{
    /**
     * Hitung Regime Score berdasarkan bobot PRD.
     * regime = 0.25*flow + 0.20*breadth + 0.15*momentum + 0.15*rupiah + 0.15*yield + 0.10*rotasi
     */
    public function calculateRegimeScore(string $date, array $driversData = []): MarketStanceDaily
    {
        $latestDate = \App\Models\MarketSnapshot::whereDate('date', '<=', $date)->max('date');
        $snapshots = $latestDate ? \App\Models\MarketSnapshot::whereDate('date', $latestDate)->get()->keyBy('symbol_or_metric') : collect();

        $ihsg = $snapshots->get('IHSG');
        
        // Dummy default data as provided by user example
        $marketData = [
            "close" => 7200,
            "ma20" => 7100,
            "ma60" => 7050,
            "ret_5d" => 0.012,
            "ret_20d" => 0.028,
            "drawdown_20d" => -0.015,
            "advancers" => 520,
            "decliners" => 159,
            "pct_above_ma20" => 0.58,
            "sector_positive_ratio" => 0.45,
            "new_high" => 12,
            "new_low" => 9,
            "foreign_net_5d" => 850000000000,
            "institutional_net_5d" => 300000000000,
            "positive_flow_days_5d" => 4,
            "total_market_value_5d" => 55000000000000,
            "positive_sectors" => 5,
            "total_sectors" => 11,
            "cyclical_positive_ratio" => 0.42,
            "leadership_concentration" => 0.55,
            "leadership_consistency_days" => 2,
            "volatility_percentile" => 0.55,
            "value_traded" => 12000000000000,
            "avg_value_20d" => 10000000000000,
            "daily_range_pct" => 0.012,
            "ihsg_return_1d" => 0.005,
        ];

        // Override dummy dengan real data kalau ada
        if ($ihsg) {
            $marketData['close'] = (float) $ihsg->value;
            $marketData['ret_5d'] = (float) $ihsg->change_pct;
            $marketData['ihsg_return_1d'] = (float) $ihsg->change_pct;

            if (!empty($ihsg->sparkline_json) && is_array($ihsg->sparkline_json)) {
                $prices = $ihsg->sparkline_json;
                $count = count($prices);
                
                if ($count >= 20) {
                    $last20 = array_slice($prices, -20);
                    $marketData['ma20'] = array_sum($last20) / 20;
                    
                    $firstOf20 = $last20[0];
                    $lastOf20 = end($last20);
                    $marketData['ret_20d'] = $firstOf20 > 0 ? ($lastOf20 - $firstOf20) / $firstOf20 : 0;
                    
                    $maxOf20 = max($last20);
                    $marketData['drawdown_20d'] = $maxOf20 > 0 ? ($lastOf20 - $maxOf20) / $maxOf20 : 0;
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

        $result = $this->calculateMarketRegime($marketData);
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

    public function calculatePriceTrendScore(array $data): int
    {
        $close = $data['close'] ?? 0;
        $ma20 = $data['ma20'] ?? 0;
        $ma60 = $data['ma60'] ?? 0;
        $ret_5d = $data['ret_5d'] ?? 0;
        $ret_20d = $data['ret_20d'] ?? 0;
        $drawdown_20d = $data['drawdown_20d'] ?? 0;

        $score = 0;

        // IHSG di atas MA20 = trend pendek sehat
        if ($close > $ma20) {
            $score += 30;
        }

        // MA20 di atas MA60 = trend menengah sehat
        if ($ma20 > $ma60) {
            $score += 25;
        }

        // Return 5 hari positif = short-term momentum positif
        if ($ret_5d > 0) {
            $score += 20;
        }

        // Return 20 hari positif = monthly momentum positif
        if ($ret_20d > 0) {
            $score += 15;
        }

        // Drawdown dari high 20 hari tidak terlalu dalam
        if ($drawdown_20d > -0.03) {
            $score += 10;
        }

        return $this->clamp($score);
    }

    public function calculateBreadthScore(array $data): int
    {
        $advancers = $data['advancers'] ?? 0;
        $decliners = $data['decliners'] ?? 0;
        $pctAboveMa20 = $data['pct_above_ma20'] ?? 0;
        $newHigh = $data['new_high'] ?? 0;
        $newLow = $data['new_low'] ?? 0;

        $score = 0;
        if ($advancers > $decliners) $score += 30;
        if ($pctAboveMa20 > 0.5) $score += 40;
        if ($newHigh > $newLow) $score += 30;
        
        return $this->clamp($score);
    }

    public function calculateFlowScore(array $data): int
    {
        $foreignNet = $data['foreign_net_5d'] ?? 0;
        $institutionalNet = $data['institutional_net_5d'] ?? 0;
        $positiveFlowDays = $data['positive_flow_days_5d'] ?? 0;

        $score = 0;
        if ($foreignNet > 0) $score += 40;
        if ($institutionalNet > 0) $score += 30;
        if ($positiveFlowDays >= 3) $score += 30;

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
