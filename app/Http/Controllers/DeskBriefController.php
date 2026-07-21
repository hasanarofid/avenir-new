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

        $date = $latestBrief ? $latestBrief->date : today()->toDateString();

        $latestTwoStances = \App\Models\MarketStanceDaily::orderBy('date', 'desc')->take(2)->get();
        $todayStance = $latestTwoStances->first();
        $yesterdayStance = $latestTwoStances->last();

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

        return Inertia::render('DeskBrief/Index', [
            'date' => $date,
            'isPreview' => $previewId && $latestBrief && $latestBrief->status !== 'published',
            'deskBrief' => $latestBrief,
            'snapshots' => $this->getSnapshots(),
            'topMovers' => $this->getTopMovers($date),
            'historicalScores' => \App\Models\MarketStanceDaily::where('date', '>=', \Carbon\Carbon::parse($date)->subYears(1))
                ->where('date', '<=', $date)
                ->orderBy('date', 'asc')
                ->get([
                    'date', 'score', 'label', 
                    'flow_momentum_v2_score', 'flow_exhaustion_score', 'reversal_probability',
                    'market_stress_composite', 'macro_stress', 'flow_internal_stress'
                ])
                ->map(function ($item) {
                    // Mock data if values are missing (for preview/testing)
                    if (is_null($item->flow_momentum_v2_score)) {
                        $item->flow_momentum_v2_score = rand(40, 70);
                        $item->flow_exhaustion_score = rand(30, 80);
                        $item->reversal_probability = rand(20, 90);
                    }
                    if (is_null($item->market_stress_composite)) {
                        $item->market_stress_composite = rand(40, 60);
                        $item->macro_stress = rand(30, 70);
                        $item->flow_internal_stress = rand(40, 80);
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
            'events' => EconomicEvent::orderBy('date', 'desc')->take(10)->get(),
            'macroCards' => MarketSnapshot::whereIn('symbol_or_metric', ['GLOBAL_GROWTH', 'US_INFLATION', 'G3_LIQUIDITY'])
                ->orderBy('date', 'desc')->get()->unique('symbol_or_metric')->values(),
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
        $yesterdayStance = $latestTwoStances->last();

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
            
        $movers = ['gainers' => [], 'losers' => []];
        if ($snap && is_array($snap->sparkline_json)) {
            $movers = $snap->sparkline_json;
        } else {
            $movers = Cache::get('sectors_top_movers', ['gainers' => [], 'losers' => []]);
        }
        
        // Attach logo_url, name, and sector from master_stocks
        $symbols = collect(array_merge($movers['gainers'] ?? [], $movers['losers'] ?? []))->pluck('symbol')->unique()->toArray();
        $stockData = \DB::table('master_stocks')->whereIn('code', $symbols)->get()->keyBy('code');
        
        foreach (['gainers', 'losers'] as $type) {
            if (isset($movers[$type])) {
                foreach ($movers[$type] as &$item) {
                    $stock = $stockData[$item['symbol']] ?? null;
                    $item['logo_url'] = $stock->logo_url ?? null;
                    $item['name'] = $stock->name ?? null;
                    $item['sector'] = $stock->sector ?? null;
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
}
