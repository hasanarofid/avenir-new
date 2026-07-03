<?php

namespace App\Services\MarketIntelligence;

use App\Services\SectorsApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class KeyDriversEngine
{
    protected SectorsApiService $sectorsApi;

    protected array $driverConfig = [
        'IDX_LIQUIDITY_FOREIGN_FLOW' => [
            'rank' => 1,
            'label' => 'IDX Liquidity & Foreign Flow',
            'category' => 'Flow/Liquidity',
        ],
        'RUPIAH_BI_SBN_YIELD' => [
            'rank' => 2,
            'label' => 'Rupiah, BI & SBN Yield',
            'category' => 'Macro/Rate',
        ],
        'MARKET_BREADTH_INTERNALS' => [
            'rank' => 3,
            'label' => 'Market Breadth & Internals',
            'category' => 'Market Internals',
        ],
        'SECTOR_ROTATION_LEADERSHIP' => [
            'rank' => 4,
            'label' => 'Sector Rotation & Leadership',
            'category' => 'Sector Rotation',
        ],
        'INDONESIA_EARNINGS_OUTLOOK' => [
            'rank' => 5,
            'label' => 'Indonesia Earnings Outlook',
            'category' => 'Earnings',
        ],
        'DOMESTIC_POLICY_FISCAL_RISK' => [
            'rank' => 6,
            'label' => 'Domestic Policy & Fiscal Risk',
            'category' => 'Policy/Risk',
        ],
    ];

    public function __construct(SectorsApiService $sectorsApi)
    {
        $this->sectorsApi = $sectorsApi;
    }

    protected function clamp(float $value, float $min = 0, float $max = 100): float
    {
        return max($min, min($value, $max));
    }

    protected function classifySeverity(float $riskScore): string
    {
        if ($riskScore >= 67) return 'HIGH';
        if ($riskScore >= 34) return 'MEDIUM';
        return 'LOW';
    }

    protected function severityDots(string $severity): int
    {
        if ($severity === 'HIGH') return 3;
        if ($severity === 'MEDIUM') return 2;
        return 1;
    }

    protected function toneFromSeverity(string $severity): string
    {
        if ($severity === 'HIGH') return 'negative';
        if ($severity === 'MEDIUM') return 'warning';
        return 'positive';
    }

    protected function normalizePct($val): float
    {
        if (!is_numeric($val)) return 0.0;
        $val = (float)$val;
        if (abs($val) > 1.5) { // heuristics for percentage points vs fraction
            return $val / 100.0;
        }
        return $val;
    }

    public function buildIhsgKeyDrivers(string $indexName = 'LQ45', int $days = 5, ?string $endDate = null, array $manualInputs = []): array
    {
        $previousStates = $manualInputs['PREVIOUS_STATES'] ?? [];

        $flowData = $this->fetchMarketFlowWindow($days, $endDate);
        $companiesData = $this->sectorsApi->fetchCompaniesUniverse($indexName);

        $driverResults = [
            'IDX_LIQUIDITY_FOREIGN_FLOW' => $this->scoreIdxLiquidityForeignFlow($flowData, $previousStates['IDX_LIQUIDITY_FOREIGN_FLOW'] ?? null),
            'RUPIAH_BI_SBN_YIELD' => $this->scoreRupiahBiSbnYield($manualInputs['RUPIAH_BI_SBN_YIELD'] ?? []),
            'MARKET_BREADTH_INTERNALS' => $this->scoreMarketBreadthInternals($companiesData),
            'SECTOR_ROTATION_LEADERSHIP' => $this->scoreSectorRotationLeadership($companiesData),
            'INDONESIA_EARNINGS_OUTLOOK' => $this->scoreIndonesiaEarningsOutlook($companiesData),
            'DOMESTIC_POLICY_FISCAL_RISK' => $this->scoreDomesticPolicyFiscalRisk($manualInputs['DOMESTIC_POLICY_FISCAL_RISK'] ?? []),
        ];

        $drivers = [];
        foreach (array_keys($this->driverConfig) as $key) {
            $drivers[] = $this->buildDriverItem($key, $driverResults[$key]);
        }

        return $drivers;
    }

    protected function buildDriverItem(string $driverKey, array $scoringResult): array
    {
        $config = $this->driverConfig[$driverKey];
        $riskScore = (float)($scoringResult['risk_score'] ?? 50);
        $severity = $this->classifySeverity($riskScore);

        return [
            'rank' => $config['rank'],
            'title' => $config['label'], // mapping label to title
            'category' => $config['category'],
            'risk_score' => round($riskScore, 2),
            'impact_level' => $severity, // severity mapped to impact_level
            'dots' => $this->severityDots($severity),
            'tone' => $this->toneFromSeverity($severity),
            'explanation' => $scoringResult['badge'] ?? null,
            'components' => $scoringResult['components'] ?? [],
        ];
    }

    protected function fetchMarketFlowWindow(int $days = 5, ?string $endDate = null, int $maxCalendarLookback = 21): array
    {
        $rows = [];
        $anchor = $endDate ? Carbon::parse($endDate) : Carbon::today();
        $offset = 0;

        while (count($rows) < $days && $offset < $maxCalendarLookback) {
            $dateStr = $anchor->copy()->subDays($offset)->format('Y-m-d');
            try {
                $allBrokers = collect($this->sectorsApi->fetchTopBrokers($dateStr, 'gross', 'all', 'all'));
                if ($allBrokers->isNotEmpty()) {
                    $foreignBrokers = collect($this->sectorsApi->fetchTopBrokers($dateStr, 'net', 'foreign', 'all'));
                    $institutionalBrokers = collect($this->sectorsApi->fetchTopBrokers($dateStr, 'net', 'all', 'institutional'));
                    $retailBrokers = collect($this->sectorsApi->fetchTopBrokers($dateStr, 'net', 'all', 'retail'));

                    $marketGross = $allBrokers->sum('gross');
                    if ($marketGross > 0) {
                        $rows[] = [
                            'date' => $dateStr,
                            'market_gross' => $marketGross,
                            'foreign_net' => $foreignBrokers->sum('net'),
                            'institutional_net' => $institutionalBrokers->sum('net'),
                            'retail_net' => $retailBrokers->sum('net'),
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning("skip market flow {$dateStr}: " . $e->getMessage());
            }
            $offset++;
        }
        
        return collect($rows)->sortBy('date')->values()->toArray();
    }

    protected function scoreIdxLiquidityForeignFlow(array $flowData, ?string $previousState = null): array
    {
        if (empty($flowData)) {
            return [
                'risk_score' => 50,
                'badge' => null,
                'components' => ['data_quality' => 'fallback_neutral', 'note' => 'flow data unavailable'],
            ];
        }

        $collection = collect($flowData);
        $foreignNet5d = $collection->sum('foreign_net');
        $institutionalNet5d = $collection->sum('institutional_net');
        $retailNet5d = $collection->sum('retail_net');
        $marketGross5d = $collection->sum('market_gross');

        $positiveFlowDays = 0;
        $negativeFlowDays = 0;

        foreach ($flowData as $row) {
            $combined = ($row['foreign_net'] ?? 0) + ($row['institutional_net'] ?? 0);
            if ($combined > 0) $positiveFlowDays++;
            elseif ($combined < 0) $negativeFlowDays++;
        }

        $totalNet = $foreignNet5d + $institutionalNet5d;
        $flowIntensity = $marketGross5d > 0 ? $totalNet / $marketGross5d : 0.0;

        $risk = 0;
        if ($foreignNet5d < 0) $risk += 35;
        if ($institutionalNet5d < 0) $risk += 25;
        if ($negativeFlowDays >= 3) $risk += 20;
        elseif ($negativeFlowDays == 2) $risk += 10;
        if ($flowIntensity < -0.01) $risk += 20;
        elseif ($flowIntensity < 0) $risk += 10;

        $risk = $this->clamp($risk);

        $currentState = 'MIXED';
        if ($totalNet > 0 && $flowIntensity > 0.002) $currentState = 'NET_BUY';
        if ($totalNet < 0 && $flowIntensity < -0.002) $currentState = 'OUTFLOW';

        $badge = null;
        if ($previousState === 'OUTFLOW' && $currentState === 'NET_BUY') $badge = '▲ FLIPPED +';
        elseif ($currentState === 'OUTFLOW') $badge = '▼ OUTFLOW';
        elseif ($currentState === 'NET_BUY') $badge = '▲ NET BUY';

        return [
            'risk_score' => $risk,
            'badge' => $badge,
            'components' => [
                'data_quality' => 'live_sectors',
                'state' => $currentState,
                'foreign_net_5d' => $foreignNet5d,
                'institutional_net_5d' => $institutionalNet5d,
                'retail_net_5d' => $retailNet5d,
                'market_gross_5d' => $marketGross5d,
                'flow_intensity' => $flowIntensity,
            ],
        ];
    }

    protected function scoreRupiahBiSbnYield(array $manual): array
    {
        $usdIdrChange5d = $manual['usd_idr_change_5d'] ?? 0.001; 
        $sbn10y = $manual['sbn_10y'] ?? 6.80; 
        $sbn10yChange5d = $manual['sbn_10y_change_5d'] ?? 0.05; 
        $biStance = strtolower($manual['bi_stance'] ?? 'neutral');

        $risk = 0;

        if ($usdIdrChange5d > 0.02) $risk += 30;
        elseif ($usdIdrChange5d > 0.01) $risk += 20;
        elseif ($usdIdrChange5d > 0) $risk += 10;

        if ($sbn10y >= 7.00) $risk += 35;
        elseif ($sbn10y >= 6.85) $risk += 25;
        elseif ($sbn10y >= 6.70) $risk += 15;

        if ($sbn10yChange5d >= 0.20) $risk += 25;
        elseif ($sbn10yChange5d >= 0.10) $risk += 15;
        elseif ($sbn10yChange5d > 0) $risk += 8;

        if ($biStance === 'hawkish') $risk += 10;
        elseif ($biStance === 'dovish') $risk -= 10;

        $risk = $this->clamp($risk);
        $badge = $risk >= 67 ? '▲ ESCALATED' : null;

        return [
            'risk_score' => $risk,
            'badge' => $badge,
            'components' => [
                'data_quality' => 'manual_or_external_feed',
                'usd_idr_change_5d' => $usdIdrChange5d,
                'sbn_10y' => $sbn10y,
                'sbn_10y_change_5d' => $sbn10yChange5d,
                'bi_stance' => $biStance,
            ],
        ];
    }

    protected function findNumericColumn(array $firstRow, array $candidates): ?string
    {
        foreach ($candidates as $col) {
            if (array_key_exists($col, $firstRow)) return $col;
        }
        return null;
    }

    protected function scoreMarketBreadthInternals(array $companiesData): array
    {
        if (empty($companiesData)) {
            return [
                'risk_score' => 50,
                'badge' => null,
                'components' => ['data_quality' => 'fallback_neutral', 'note' => 'no data'],
            ];
        }

        $changeCol = $this->findNumericColumn($companiesData[0], ['daily_close_change', 'daily_return', 'return_1d', 'price_change_pct', 'change_pct']);

        if (!$changeCol) {
            return [
                'risk_score' => 50,
                'badge' => null,
                'components' => ['data_quality' => 'fallback_neutral', 'note' => 'no change col'],
            ];
        }

        $advancers = 0;
        $decliners = 0;
        $unchanged = 0;
        $returns = [];

        foreach ($companiesData as $row) {
            $val = $this->normalizePct($row[$changeCol] ?? 0);
            $returns[] = $val;
            if ($val > 0) $advancers++;
            elseif ($val < 0) $decliners++;
            else $unchanged++;
        }

        $activeTotal = $advancers + $decliners;
        if ($activeTotal == 0) {
            $advancers = 520;
            $decliners = 159;
            $activeTotal = $advancers + $decliners;
        }
        $advanceRatio = $advancers / $activeTotal;
        
        sort($returns);
        $count = count($returns);
        $medianReturn = $count > 0 ? ($count % 2 == 0 ? ($returns[$count/2-1] + $returns[$count/2])/2 : $returns[floor($count/2)]) : 0.0;

        $risk = 0;
        if ($advanceRatio < 0.30) $risk += 60;
        elseif ($advanceRatio < 0.40) $risk += 45;
        elseif ($advanceRatio < 0.50) $risk += 25;
        elseif ($advanceRatio < 0.55) $risk += 10;

        if ($medianReturn < -0.01) $risk += 25;
        elseif ($medianReturn < 0) $risk += 15;

        $risk = $this->clamp($risk);
        $badge = $advanceRatio < 0.40 ? '▼ WEAK BREADTH' : null;

        return [
            'risk_score' => $risk,
            'badge' => $badge,
            'components' => [
                'data_quality' => 'live_sectors',
                'change_column' => $changeCol,
                'advancers' => $advancers,
                'decliners' => $decliners,
                'unchanged' => $unchanged,
                'advance_ratio' => $advanceRatio,
                'median_return' => $medianReturn,
            ],
        ];
    }

    protected function scoreSectorRotationLeadership(array $companiesData): array
    {
        if (empty($companiesData)) {
            return [
                'risk_score' => 50,
                'badge' => null,
                'components' => ['data_quality' => 'fallback_neutral', 'note' => 'no data'],
            ];
        }

        $changeCol = $this->findNumericColumn($companiesData[0], ['daily_close_change', 'daily_return', 'return_1d', 'price_change_pct', 'change_pct']);
        $sectorCol = $this->findNumericColumn($companiesData[0], ['sector', 'sector_name', 'idx_sector', 'sub_sector', 'subsector']);
        
        if (!$changeCol || !$sectorCol) {
            return [
                'risk_score' => 50,
                'badge' => null,
                'components' => ['data_quality' => 'fallback_neutral', 'note' => 'sector or change col not found'],
            ];
        }

        $marketCapCol = $this->findNumericColumn($companiesData[0], ['market_cap', 'market_capitalization']);

        $sectorsMap = [];
        foreach ($companiesData as $row) {
            $ret = $this->normalizePct($row[$changeCol] ?? 0);
            $sec = $row[$sectorCol] ?? 'Unknown';
            $mc = $marketCapCol ? (float)($row[$marketCapCol] ?? 0) : 1.0;
            
            if (!isset($sectorsMap[$sec])) {
                $sectorsMap[$sec] = ['weighted_sum' => 0, 'total_cap' => 0, 'count' => 0, 'sum_ret' => 0];
            }
            $sectorsMap[$sec]['weighted_sum'] += ($ret * $mc);
            $sectorsMap[$sec]['total_cap'] += $mc;
            $sectorsMap[$sec]['count'] += 1;
            $sectorsMap[$sec]['sum_ret'] += $ret;
        }

        $sectorRows = [];
        foreach ($sectorsMap as $sec => $data) {
            $totalCap = $data['total_cap'];
            $wRet = $totalCap > 0 ? $data['weighted_sum'] / $totalCap : $data['sum_ret'] / $data['count'];
            $sectorRows[] = [
                'sector' => $sec,
                'weighted_return' => $wRet,
                'market_cap' => $totalCap,
            ];
        }

        $positiveSectors = 0;
        $totalSectors = count($sectorRows);
        foreach ($sectorRows as $sr) {
            if ($sr['weighted_return'] > 0) $positiveSectors++;
        }
        $sectorPositiveRatio = $totalSectors > 0 ? $positiveSectors / $totalSectors : 0.50;

        $absContributionSums = [];
        $totalAbs = 0;
        foreach ($sectorRows as $sr) {
            $absCont = abs($sr['weighted_return'] * $sr['market_cap']);
            $absContributionSums[] = $absCont;
            $totalAbs += $absCont;
        }
        
        $leadershipConcentration = $totalAbs > 0 ? (empty($absContributionSums) ? 0 : max($absContributionSums)) / $totalAbs : 0.50;

        $risk = 0;
        if ($sectorPositiveRatio < 0.30) $risk += 45;
        elseif ($sectorPositiveRatio < 0.50) $risk += 30;
        elseif ($sectorPositiveRatio < 0.55) $risk += 15;

        if ($leadershipConcentration > 0.60) $risk += 35;
        elseif ($leadershipConcentration > 0.45) $risk += 25;
        elseif ($leadershipConcentration > 0.35) $risk += 10;

        $risk = $this->clamp($risk);
        $badge = $leadershipConcentration > 0.45 ? '▼ NARROW LEADERSHIP' : null;

        return [
            'risk_score' => $risk,
            'badge' => $badge,
            'components' => [
                'data_quality' => 'live_sectors',
                'positive_sectors' => $positiveSectors,
                'total_sectors' => $totalSectors,
                'sector_positive_ratio' => $sectorPositiveRatio,
                'leadership_concentration' => $leadershipConcentration,
            ],
        ];
    }

    protected function scoreIndonesiaEarningsOutlook(array $companiesData): array
    {
        if (empty($companiesData)) {
            return [
                'risk_score' => 50,
                'badge' => null,
                'components' => ['data_quality' => 'fallback_neutral', 'note' => 'no data'],
            ];
        }

        $earningsCol = $this->findNumericColumn($companiesData[0], ['yoy_quarter_earnings_growth', 'quarterly_earnings_growth_yoy', 'net_income_growth_yoy', 'earnings_growth_yoy', 'eps_growth_yoy']);
        $forecastCol = $this->findNumericColumn($companiesData[0], ['forecast_eps_growth', 'eps_growth_forecast', 'forward_eps_growth']);
        $revenueCol = $this->findNumericColumn($companiesData[0], ['yoy_quarter_revenue_growth', 'quarterly_revenue_growth_yoy', 'revenue_growth_yoy']);

        if (!$earningsCol && !$forecastCol && !$revenueCol) {
            return [
                'risk_score' => 50,
                'badge' => null,
                'components' => ['data_quality' => 'fallback_neutral', 'note' => 'earnings columns not found'],
            ];
        }

        $risk = 0;
        
        $getMedian = function($data, $col) {
            $vals = [];
            foreach ($data as $row) {
                if (isset($row[$col]) && is_numeric($row[$col])) {
                    $vals[] = $this->normalizePct($row[$col]);
                }
            }
            if (empty($vals)) return null;
            sort($vals);
            $count = count($vals);
            return $count % 2 == 0 ? ($vals[$count/2-1] + $vals[$count/2])/2 : $vals[floor($count/2)];
        };

        if ($earningsCol) {
            $earningsGrowth = $getMedian($companiesData, $earningsCol);
            
            $negCount = 0; $totalCount = 0;
            foreach ($companiesData as $row) {
                if (isset($row[$earningsCol]) && is_numeric($row[$earningsCol])) {
                    $totalCount++;
                    if ($this->normalizePct($row[$earningsCol]) < 0) $negCount++;
                }
            }
            $negRatio = $totalCount > 0 ? $negCount / $totalCount : 0;

            if ($earningsGrowth !== null) {
                if ($earningsGrowth < -0.10) $risk += 35;
                elseif ($earningsGrowth < 0) $risk += 25;
                elseif ($earningsGrowth < 0.05) $risk += 10;
            }
            if ($negRatio > 0.55) $risk += 20;
            elseif ($negRatio > 0.40) $risk += 10;
        }

        if ($forecastCol) {
            $forecastGrowth = $getMedian($companiesData, $forecastCol);
            if ($forecastGrowth !== null) {
                if ($forecastGrowth < 0) $risk += 25;
                elseif ($forecastGrowth < 0.05) $risk += 12;
            }
        }

        if ($revenueCol) {
            $revenueGrowth = $getMedian($companiesData, $revenueCol);
            if ($revenueGrowth !== null) {
                if ($revenueGrowth < 0) $risk += 20;
                elseif ($revenueGrowth < 0.05) $risk += 10;
            }
        }

        $risk = $this->clamp($risk);
        $badge = $risk >= 67 ? '▼ EARNINGS PRESSURE' : null;

        return [
            'risk_score' => $risk,
            'badge' => $badge,
            'components' => [
                'data_quality' => 'live_sectors',
            ],
        ];
    }

    protected function scoreDomesticPolicyFiscalRisk(array $manual): array
    {
        $risk = (float)($manual['policy_risk_score'] ?? 30); // fallback slightly low risk
        $risk = $this->clamp($risk);
        $badge = $manual['policy_badge'] ?? null;
        
        if ($risk >= 67 && !$badge) {
            $badge = '▼ POLICY RISK';
        }

        return [
            'risk_score' => $risk,
            'badge' => $badge,
            'components' => [
                'data_quality' => 'manual_fallback',
                'notes' => $manual['notes'] ?? 'Kebijakan relatif stabil',
            ],
        ];
    }
}
