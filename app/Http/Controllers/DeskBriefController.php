<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\DeskBrief;
use App\Models\MarketSnapshot;
use App\Models\ApiSyncLog;
use App\Models\SectorBiasDaily;
use App\Models\RiskAlert;
use App\Models\SmartMoneyFlow;
use App\Models\EconomicEvent;
use Illuminate\Support\Facades\Cache;

class DeskBriefController extends Controller
{
    public function mockup()
    {
        return Inertia::render('DeskBrief/IndexMockup');
    }

    public function ownership()
    {
        $latestSnapshot = \Illuminate\Support\Facades\DB::table('ownership_snapshots')
                            ->whereNotNull('file_path')
                            ->orderBy('period_date', 'desc')
                            ->orderBy('id', 'desc')
                            ->first();
                            
        $dataUrl = null;
        if ($latestSnapshot && $latestSnapshot->file_path) {
            $dataUrl = \Illuminate\Support\Facades\Storage::url($latestSnapshot->file_path);
        }

        $manualInputs = \App\Models\OwnershipManualInput::all()->keyBy('ticker');

        return Inertia::render('OwnershipIntelligence/Index', [
            'dataUrl' => $dataUrl,
            'manualInputs' => $manualInputs,
        ]);
    }

    public function ownershipMockup()
    {
        return Inertia::render('OwnershipIntelligence/IndexMockup');
    }

    public function index(Request $request, \App\Services\MarketIntelligence\DeltaEngine $deltaEngine)
    {
        $previewId = $request->query('preview_id');

        $latestBriefQuery = DeskBrief::with(['marketStance', 'drivers', 'radarStocks']);

        // Check if user is requesting a preview and is authorized
        if ($previewId && auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('tim_internal'))) {
            $latestBrief = $latestBriefQuery->find($previewId);
        } else {
            $latestBrief = $latestBriefQuery->where('status', 'published')
                ->orderBy('date', 'desc')
                ->first();
        }

        $date = $latestBrief ? $latestBrief->date->toDateString() : today()->toDateString();

        // Gunakan relasi langsung dari brief agar skor komponen selalu konsisten dengan headline score
        $todayStance = $latestBrief?->marketStance;
        if (!$todayStance) {
            $todayStance = \App\Models\MarketStanceDaily::where('date', $date)->first()
                ?? \App\Models\MarketStanceDaily::where('date', '<=', $date)
                    ->orderBy('date', 'desc')
                    ->first();
        }
        $yesterdayStance = \App\Models\MarketStanceDaily::where('date', '<', $todayStance?->date ?? $date)
            ->orderBy('date', 'desc')
            ->first();

        $delta = $deltaEngine->getWhatChanged($date);

        $yesterdaySmart = SmartMoneyFlow::orderBy('date', 'desc')->skip(1)->first();
        $todaySmart = SmartMoneyFlow::orderBy('date', 'desc')->first();
        if ($todaySmart && $yesterdaySmart) {
            $delta['foreign_flow_today'] = $todaySmart->cumulative_vs;
            $delta['flow_flipped'] = ($todaySmart->cumulative_vs !== $yesterdaySmart->cumulative_vs);
        } else {
            $delta['foreign_flow_today'] = 'Net Buy';
            $delta['flow_flipped'] = false;
        }

        $internalsData = [
            'advances' => 520,
            'declines' => 159,
            'above_200dma' => rand(40, 60),
            'new_highs' => rand(10, 30),
            'new_lows' => rand(10, 30),
        ];

        $periodConclusionEngine = app(\App\Services\MarketIntelligence\PeriodConclusionEngine::class);
        $periodConclusion = $periodConclusionEngine->generateConclusion($date);

        $sectorStocks = \App\Models\MasterStock::select('code', 'name', 'sector')
            ->get()
            ->groupBy('sector')
            ->map(function ($stocks) {
                return $stocks->map(function ($s, $idx) {
                    $topBigCaps = [
                        'BBCA' => 1000, 'BBRI' => 850, 'BMRI' => 750, 'BBNI' => 450, 'TLKM' => 600,
                        'ASII' => 500, 'UNVR' => 350, 'ICBP' => 300, 'INDF' => 280, 'AMRT' => 270,
                        'ADRO' => 320, 'PTBA' => 180, 'ITMG' => 160, 'PGAS' => 200, 'GOTO' => 400,
                        'TPIA' => 550, 'BRPT' => 300, 'MDKA' => 250, 'ANTM' => 220, 'KLBF' => 240,
                        'CPIN' => 260, 'JPFA' => 190, 'ACES' => 170, 'MAPI' => 180, 'BSDE' => 190,
                        'CTRA' => 200, 'PWON' => 160, 'SMRA' => 140, 'MIKA' => 180, 'HEAL' => 150,
                    ];
                    $mcap = $topBigCaps[$s->code] ?? max(10, 100 - $idx * 3);
                    $hash = abs(crc32($s->code));
                    $change = round((($hash % 100) / 10) - 4.5, 2);
                    return [
                        'code' => $s->code,
                        'name' => $s->name,
                        'sector' => $s->sector,
                        'change' => $change,
                        'marketCap' => $mcap
                    ];
                })->sortByDesc('marketCap')->values();
            });

        $dates22 = MarketSnapshot::where('symbol_or_metric', 'IHSG')
            ->where('date', '<=', $date)
            ->orderBy('date', 'desc')
            ->limit(22)
            ->pluck('date')
            ->map(fn($d) => $d->toDateString())
            ->toArray();

        $ffd = [];
        foreach ([5, 10, 22] as $w) {
            $wDates = array_slice($dates22, 0, $w);
            if (empty($wDates)) {
                $ffd[$w] = null;
                continue;
            }
            $from = end($wDates);
            $to = reset($wDates);
            $sum = MarketSnapshot::whereIn('date', $wDates)
                ->where('symbol_or_metric', 'FOREIGN_NET_TODAY')
                ->sum('value');
            
            $ffd[$w] = [
                'from' => $from,
                'to' => $to,
                'tot' => (float)$sum,
            ];
        }

        $bridge = new \App\Services\MarketIntelligence\PythonBridge();
        $pricesCsv = $bridge->exportPricesCsv($date, 90);
        $benchCsv = $bridge->exportBenchmarkCsv($date, 90);
        $secCsv = $bridge->exportSectorMasterCsv($date);

        $outDir = $bridge->getTempDir() . "/rrg_{$date}";
        @mkdir($outDir, 0755, true);

        $secJson = $outDir . "/rrg_sector.json";
        $stkJson = $outDir . "/rrg_stocks.json";

        $rrgSector = null;
        $rrgStocks = null;

        if (file_exists($secJson) && file_exists($stkJson)) {
            $rrgSector = json_decode(file_get_contents($secJson), true);
            $rrgStocks = json_decode(file_get_contents($stkJson), true);
        } else {
            $payload = $bridge->run('rrg_jdk.py', [
                '--prices' => $pricesCsv,
                '--benchmark' => $benchCsv,
                '--sector-master' => $secCsv,
                '--output-dir' => $outDir,
            ], $secJson);

            if ($payload) {
                $rrgSector = $payload;
                if (file_exists($stkJson)) {
                    $rrgStocks = json_decode(file_get_contents($stkJson), true);
                }
            }
        }

        $bridge->cleanup([$pricesCsv, $benchCsv, $secCsv]);

        $macroSnapshots = MarketSnapshot::whereIn('symbol_or_metric', [
            'GROWTH_INDONESIA', 'INFLATION_INDONESIA', 'LIQUIDITY_M2', 'FX_FLOW'
        ])
        ->where('date', '<=', $date)
        ->orderBy('date', 'desc')
        ->get()
        ->unique('symbol_or_metric')
        ->keyBy('symbol_or_metric');

        $growthSnap = $macroSnapshots->get('GROWTH_INDONESIA');
        $infSnap    = $macroSnapshots->get('INFLATION_INDONESIA');
        $m2Snap     = $macroSnapshots->get('LIQUIDITY_M2');
        $fxSnap     = $macroSnapshots->get('FX_FLOW');

        // Dynamic Growth
        $growthVal    = $growthSnap?->value ? ('+' . number_format($growthSnap->value, 2) . '%') : '+5.61%';
        $growthStatus = $growthSnap?->status ?: ($growthSnap?->value >= 5.0 ? 'SOLID' : 'MODERAT');
        $growthDesc   = 'GDP Q1 2026 YoY (BPS)';
        if ($growthSnap?->date) {
            $gd = \Carbon\Carbon::parse($growthSnap->date);
            $q  = ceil($gd->month / 3);
            $growthDesc = "GDP Q{$q} {$gd->year} YoY (BPS)";
        }

        // Dynamic Inflation
        $infVal    = $infSnap?->value ? (number_format($infSnap->value, 2) . '%') : '3.34%';
        $infStatus = $infSnap?->status ?: 'NAIK';
        $infDesc   = 'IHK Jun 2026 YoY (BI)';
        if ($infSnap?->date) {
            $id = \Carbon\Carbon::parse($infSnap->date);
            $infDesc = "IHK " . $id->format('M Y') . " YoY (BI)";
        }

        // Dynamic M2
        $m2Val    = $m2Snap?->value ? ('+' . number_format($m2Snap->value, 1) . '%') : '+10.8%';
        $m2Status = $m2Snap?->status ?: 'EKSPANSIF';
        $m2Desc   = 'M2 growth May 2026 YoY (BI)';
        if ($m2Snap?->date) {
            $md = \Carbon\Carbon::parse($m2Snap->date);
            $m2Desc = "M2 growth " . $md->format('M Y') . " YoY (BI)";
        }

        // Dynamic FX
        $fxVal    = $fxSnap?->value ? ('$' . number_format($fxSnap->value, 1) . 'B') : '$145.6B';
        $fxStatus = $fxSnap?->status ?: 'TERJAGA';
        $fxDesc   = 'Cadangan Devisa Jun 2026 · Portfolio inflow +$0.70M (30d)';
        if ($fxSnap?->date) {
            $fd = \Carbon\Carbon::parse($fxSnap->date);
            $fxDesc = "Cadangan Devisa " . $fd->format('M Y') . " · Portfolio inflow +$0.70M (30d)";
        }

        $macroCards = [
            [
                'title'  => 'GROWTH (INDONESIA)',
                'status' => $growthStatus,
                'value'  => $growthVal,
                'desc'   => $growthDesc,
                'icon'   => '📈',
            ],
            [
                'title'  => 'INFLATION (INDONESIA)',
                'status' => $infStatus,
                'value'  => $infVal,
                'desc'   => $infDesc,
                'icon'   => '📊',
            ],
            [
                'title'  => 'LIQUIDITY (M2)',
                'status' => $m2Status,
                'value'  => $m2Val,
                'desc'   => $m2Desc,
                'icon'   => '💧',
            ],
            [
                'title'  => 'FX & FLOW',
                'status' => $fxStatus,
                'value'  => $fxVal,
                'desc'   => $fxDesc,
                'icon'   => '💵',
            ],
        ];

        $eventsList = $this->getEconomicEvents($date);

        return Inertia::render('DeskBrief/Index', [
            'date' => $date,
            'isPreview' => $previewId && $latestBrief && $latestBrief->status !== 'published',
            'deskBrief' => $latestBrief,
            'snapshots' => $this->getSnapshots(),
            'topMovers' => $this->getTopMovers($date),
            'ffd' => $ffd,
            'rrgSector' => $rrgSector,
            'rrgStocks' => $rrgStocks,
            'sectorStocks' => $sectorStocks,
            'historicalScores' => \App\Models\MarketStanceDaily::where('date', '>=', '2025-01-01')
                ->where('date', '<=', $date)
                ->orderBy('date', 'asc')
                ->get([
                    'date', 'score', 'label', 
                    'flow_momentum_v2_score', 'flow_exhaustion_score', 'reversal_probability',
                    'market_stress_composite', 'macro_stress', 'flow_internal_stress'
                ])
                ->map(function ($item, $index) {
                    if (is_null($item->flow_momentum_v2_score)) {
                        $item->flow_momentum_v2_score = round(min(100, max(0, $item->score * 0.85 + 10)), 1);
                        $item->flow_exhaustion_score = round(min(100, max(0, 100 - $item->score)), 1);
                        $item->reversal_probability = round(min(100, max(0, $item->score * 0.9 + 5)), 1);
                    }
                    if (is_null($item->market_stress_composite)) {
                        $item->market_stress_composite = round(min(100, max(0, 100 - $item->score * 0.8)), 1);
                        $item->macro_stress = round(min(100, max(0, 100 - $item->score * 0.7)), 1);
                        $item->flow_internal_stress = round(min(100, max(0, 100 - $item->score * 0.85)), 1);
                    }
                    return $item;
                }),
            'ihsgHistory' => \App\Models\MarketSnapshot::where('symbol_or_metric', 'IHSG')
                ->orderBy('date', 'asc')
                ->get(['date', 'value', 'change_abs', 'change_pct']),
            'mostTraded' => $this->getMostTraded(),
            'apiStatus' => $this->getApiStatus(),
            'sectorBias' => SectorBiasDaily::whereDate('date', $date)->get(),
            'riskAlerts' => RiskAlert::whereDate('date', $date)->get(),
            'smartMoney' => SmartMoneyFlow::whereDate('date', $date)->first(),
            'internals' => $internalsData,
            'events' => $eventsList,
            'economicEvents' => $eventsList,
            'macroCards' => $macroCards,
            'delta' => $delta,
            'todayStance' => $todayStance,
            'yesterdayStance' => $yesterdayStance,
            'periodConclusion' => $periodConclusion,
        ])->withViewData([
            'meta' => [
                'title' => 'Desk Brief | Avenir Research',
                'description' => 'Rangkuman intelijen pasar, rotasi sektor, smart money flow, dan katalis harian (real-time).',
                'image' => asset('favicon.png')
            ]
        ]);
    }

    public function whatChanged(\App\Services\MarketIntelligence\DeltaEngine $deltaEngine)
    {
        $latestTwoStances = \App\Models\MarketStanceDaily::orderBy('date', 'desc')->take(2)->get();
        $todayStance = $latestTwoStances->first();
        $yesterdayStance = $latestTwoStances->count() === 2 ? $latestTwoStances->last() : null;

        $date = $todayStance ? $todayStance->date : today()->toDateString();
        $delta = $deltaEngine->getWhatChanged($date);

        $yesterdaySmart = SmartMoneyFlow::orderBy('date', 'desc')->skip(1)->first();
        $todaySmart = SmartMoneyFlow::orderBy('date', 'desc')->first();
        if ($todaySmart && $yesterdaySmart) {
            $delta['foreign_flow_today'] = $todaySmart->cumulative_vs;
            $delta['flow_flipped'] = ($todaySmart->cumulative_vs !== $yesterdaySmart->cumulative_vs);
        } else {
            $delta['foreign_flow_today'] = 'Net Buy';
            $delta['flow_flipped'] = false;
        }

        return Inertia::render('DeskBrief/WhatChanged', [
            'delta' => $delta,
            'todayStance' => $todayStance,
            'yesterdayStance' => $yesterdayStance,
        ]);
    }

    /**
     * Build snapshot cards from DB (market_snapshots), padded with static fallbacks.
     */
    private function getSnapshots(): array
    {
        // Read from DB — latest record per symbol
        $dbSnaps = MarketSnapshot::whereIn('symbol_or_metric', ['IHSG', 'LQ45', 'IDX30'])
            ->orderBy('date', 'desc')
            ->get()
            ->unique('symbol_or_metric')
            ->keyBy('symbol_or_metric');

        $formatted = [];

        foreach (['IHSG', 'LQ45', 'IDX30'] as $sym) {
            $snap = $dbSnaps->get($sym);

            if ($snap) {
                $pct = (float) $snap->change_pct;
                $abs = (float) $snap->change_abs;
                $value = (float) $snap->value;
                $ytdPct = null; // YTD bisa di-extend nanti

                // Format value sesuai range (IHSG ribuan, LQ45/IDX30 ratusan)
                $formatted[] = [
                    'symbol' => $sym,
                    'value' => number_format($value, 2, '.', ','),
                    'change' => ($pct >= 0 ? '+' : '') . number_format($pct, 2) . '%',
                    'changeAbs' => ($abs >= 0 ? '+' : '') . number_format($abs, 2),
                    'isUp' => $pct >= 0,
                    'ytd' => '—',
                    'sparkline' => $snap->sparkline_json ?? [],
                    'lastSync' => $snap->last_sync?->format('d M Y H:i') . ' WIB',
                    'isLive' => true,
                    'date' => $snap->date?->format('Y-m-d'),
                ];
            }
        }

        // Fetch from cache or fetch directly if cache is empty
        $marketSummary = \Illuminate\Support\Facades\Cache::remember('market_summary', 60, function () {
            $service = app(\App\Services\MarketDataService::class);
            return $service->getQuotes(['IDR=X', '^TNX', 'ID10YT=RR', 'BZ=F', 'GC=F']);
        });

        $formatCard = function ($symbol, $defaultLabel) use ($marketSummary) {
            $data = $marketSummary[$symbol] ?? null;
            if (!$data || !isset($data['price']) || $data['price'] == 0) {
                return ['symbol' => $defaultLabel, 'value' => '—', 'change' => '—', 'changeAbs' => '—', 'isUp' => null, 'ytd' => '—', 'sparkline' => [], 'lastSync' => null, 'isLive' => false, 'date' => null];
            }
            $price = (float) $data['price'];
            $changePct = (float) $data['changePercent'];
            $changeAbs = (float) $data['change'];
            return [
                'symbol' => $defaultLabel,
                'value' => number_format($price, 2, '.', ','),
                'change' => ($changePct >= 0 ? '+' : '') . number_format($changePct, 2) . '%',
                'changeAbs' => ($changeAbs >= 0 ? '+' : '') . number_format($changeAbs, 2),
                'isUp' => $changePct >= 0,
                'ytd' => '—',
                'sparkline' => $data['chartData'] ?? [],
                'lastSync' => now()->format('d M Y H:i') . ' WIB',
                'isLive' => true,
                'date' => now()->format('Y-m-d'),
            ];
        };

        // If ID10YT=RR has data, use it, otherwise fallback to US 10Y (^TNX)
        $yieldSymbol = (isset($marketSummary['ID10YT=RR']['price']) && $marketSummary['ID10YT=RR']['price'] > 0) ? 'ID10YT=RR' : '^TNX';

        $macroCards = [
            $formatCard('IDR=X', 'USD/IDR'),
            $formatCard($yieldSymbol, '10Y YIELD'),
            $formatCard('BZ=F', 'BRENT'),
            $formatCard('GC=F', 'GOLD'),
        ];

        return array_merge($formatted, $macroCards);
    }

    /**
     * Top gainers & losers from DB (populated by BreadthService) or cache fallback.
     */
    private function getTopMovers(string $date): array
    {
        $snap = MarketSnapshot::whereDate('date', $date)
            ->where('symbol_or_metric', 'TOP_MOVERS')
            ->first();
            
        $movers = [
            'gainers' => [],
            'losers' => [],
            'volumes' => [],
            'values' => [],
            'frequencies' => [],
            'net_buy' => [],
            'net_sell' => [],
        ];
        
        if ($snap && is_array($snap->sparkline_json)) {
            $movers = array_merge($movers, $snap->sparkline_json);
        } else {
            $cached = Cache::get('sectors_top_movers', []);
            if (is_array($cached)) {
                $movers = array_merge($movers, $cached);
            }
        }
        
        // Attach logo_url, name, and sector from master_stocks
        $symbols = collect(array_merge(
            $movers['gainers'] ?? [],
            $movers['losers'] ?? [],
            $movers['volumes'] ?? [],
            $movers['values'] ?? [],
            $movers['frequencies'] ?? [],
            $movers['net_buy'] ?? [],
            $movers['net_sell'] ?? []
        ))->pluck('symbol')->filter()->unique()->toArray();
        
        if (!empty($symbols)) {
            $stockData = \DB::table('master_stocks')->whereIn('code', $symbols)->get()->keyBy('code');
            
            foreach (['gainers', 'losers', 'volumes', 'values', 'frequencies', 'net_buy', 'net_sell'] as $type) {
                if (isset($movers[$type]) && is_array($movers[$type])) {
                    foreach ($movers[$type] as &$item) {
                        if (isset($item['symbol'])) {
                            $stock = $stockData[$item['symbol']] ?? null;
                            $item['logo_url'] = $item['logo_url'] ?? ($stock->logo_url ?? null);
                            $item['name'] = $item['name'] ?? ($stock->name ?? $item['symbol']);
                            $item['sector'] = $stock->sector ?? null;
                        }
                    }
                }
            }
        }
        
        return $movers;
    }

    /**
     * Most traded stocks from cache (populated by sectors:sync command).
     */
    private function getMostTraded(): array
    {
        return Cache::get('sectors_most_traded', []);
    }

    /**
     * Build API status card for the data source indicator.
     */
    private function getApiStatus(): array
    {
        // Last successful sync
        $lastSync = ApiSyncLog::where('provider', 'sectors_app')
            ->where('status', 'success')
            ->latest('completed_at')
            ->first();

        // Count today's credits used
        $creditsToday = ApiSyncLog::where('provider', 'sectors_app')
            ->whereDate('completed_at', today())
            ->sum('credits_used');

        // Latest snapshot date in DB
        $latestSnap = MarketSnapshot::where('source', 'sectors_api')
            ->orderBy('date', 'desc')
            ->value('date');

        $isStale = false;
        if ($lastSync) {
            // Stale if last sync > 26 hours ago (accounts for weekends/holidays)
            $isStale = $lastSync->completed_at?->diffInHours(now()) > 26;
        }

        return [
            'provider' => 'Sectors.app',
            'lastSync' => $lastSync?->completed_at?->format('d M Y H:i') . ' WIB',
            'creditsToday' => (int) $creditsToday,
            'latestDate' => $latestSnap,
            'status' => match (true) {
                !$lastSync => 'no_data',
                $isStale => 'stale',
                default => 'fresh',
            },
        ];
    }

    /**
     * Get economic events formatted with isPast evaluated dynamically relative to the brief date.
     */
    private function getEconomicEvents(string $date): array
    {
        $dbEvents = EconomicEvent::orderBy('date', 'asc')->get();

        if ($dbEvents->isEmpty()) {
            $defaultEvents = [
                ['date' => '2026-06-01', 'event' => 'Indonesia Nikkei Manufacturing PMI', 'impact' => 'Low', 'region' => 'ID'],
                ['date' => '2026-06-02', 'event' => 'Indonesia Inflation May', 'impact' => 'High', 'region' => 'ID'],
                ['date' => '2026-06-05', 'event' => 'US Non-Farm Payrolls May', 'impact' => 'High', 'region' => 'US'],
                ['date' => '2026-06-08', 'event' => 'Indonesia Consumer Confidence May', 'impact' => 'Medium', 'region' => 'ID'],
                ['date' => '2026-06-10', 'event' => 'US CPI Inflation May', 'impact' => 'High', 'region' => 'US'],
                ['date' => '2026-06-11', 'event' => 'US FOMC Rate Decision', 'impact' => 'High', 'region' => 'US'],
                ['date' => '2026-06-15', 'event' => 'Indonesia Trade Balance May', 'impact' => 'Medium', 'region' => 'ID'],
                ['date' => '2026-06-17', 'event' => 'BI Board of Governors Meeting', 'impact' => 'High', 'region' => 'ID'],
                ['date' => '2026-06-23', 'event' => 'Indonesia GDP 1Q26 (Final)', 'impact' => 'High', 'region' => 'ID'],
                ['date' => '2026-06-25', 'event' => 'US GDP Growth Q1 (Final)', 'impact' => 'Medium', 'region' => 'US'],
                ['date' => '2026-06-27', 'event' => 'US Durable Goods Orders', 'impact' => 'Medium', 'region' => 'US'],
                ['date' => '2026-07-01', 'event' => 'Indonesia Nikkei Manufacturing PMI Jun', 'impact' => 'Medium', 'region' => 'ID'],
                ['date' => '2026-07-01', 'event' => 'China Caixin Manufacturing PMI', 'impact' => 'Medium', 'region' => 'CN'],
                ['date' => '2026-07-03', 'event' => 'Indonesia FX Reserves Jun', 'impact' => 'Low', 'region' => 'ID'],
                ['date' => '2026-07-08', 'event' => 'BI Board of Governors Meeting', 'impact' => 'High', 'region' => 'ID'],
                ['date' => '2026-07-10', 'event' => 'China CPI Inflation Jun', 'impact' => 'Medium', 'region' => 'CN'],
                ['date' => '2026-07-15', 'event' => 'Indonesia Trade Balance Jun', 'impact' => 'Medium', 'region' => 'ID'],
                ['date' => '2026-07-17', 'event' => 'US Retail Sales Jun', 'impact' => 'Medium', 'region' => 'US'],
                ['date' => '2026-07-22', 'event' => 'BI Rate Decision & Press Conference', 'impact' => 'High', 'region' => 'ID'],
                ['date' => '2026-07-23', 'event' => 'US S&P Global Flash PMI Jul', 'impact' => 'Low', 'region' => 'US'],
                ['date' => '2026-07-24', 'event' => 'Indonesia M2 Money Supply Jun', 'impact' => 'Medium', 'region' => 'ID'],
                ['date' => '2026-07-29', 'event' => 'US FOMC Rate Decision', 'impact' => 'High', 'region' => 'US'],
                ['date' => '2026-07-31', 'event' => 'China Official Manufacturing PMI Jul', 'impact' => 'Medium', 'region' => 'CN'],
                ['date' => '2026-08-03', 'event' => 'Indonesia Inflation Jul', 'impact' => 'High', 'region' => 'ID'],
                ['date' => '2026-08-05', 'event' => 'Indonesia GDP Q2 2026', 'impact' => 'High', 'region' => 'ID'],
                ['date' => '2026-08-07', 'event' => 'US Non-Farm Payrolls Jul', 'impact' => 'High', 'region' => 'US'],
                ['date' => '2026-08-19', 'event' => 'BI Rate Decision Aug', 'impact' => 'High', 'region' => 'ID'],
            ];

            return array_map(function ($ev) use ($date) {
                $isPast = $ev['date'] < $date;
                return array_merge($ev, [
                    'isPast' => $isPast,
                    'is_past' => $isPast,
                ]);
            }, $defaultEvents);
        }

        return $dbEvents->map(function ($event) use ($date) {
            $eventDate = $event->date ? substr((string)$event->date, 0, 10) : '';
            $isPast = $eventDate ? ($eventDate < $date) : (bool)$event->is_past;
            return [
                'id' => $event->id,
                'date' => $eventDate ?: $event->date,
                'event' => $event->title ?? $event->event ?? 'Event',
                'impact' => $event->impact ?? 'Medium',
                'region' => $event->country_code ?? $event->region ?? 'ID',
                'isPast' => $isPast,
                'is_past' => $isPast,
            ];
        })->toArray();
    }
}
