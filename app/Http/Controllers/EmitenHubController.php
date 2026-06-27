<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Ticker;

class EmitenHubController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticker::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('symbol', 'like', '%' . $request->search . '%')
                  ->orWhere('company_name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->sector) {
            $query->where('sector', $request->sector);
        }

        // Other filters (Market Cap, PER, Growth, Yield, Papan, Index) are not in DB yet.
        // We will pass them back to the frontend to keep the state.
        
        $tickers = $query->paginate(24)->withQueryString();

        // Get unique sectors for the dropdown
        $sectors = Ticker::whereNotNull('sector')->distinct()->pluck('sector');

        return Inertia::render('EmitenHub/Index', [
            'tickers' => $tickers,
            'sectors' => $sectors,
            'filters' => $request->only([
                'search', 'sector', 'market_cap', 'per', 'growth', 'yield', 'papan', 'index_board'
            ]),
            'sectorApiKey' => config('services.sectors.key')
        ]);
    }

    public function show($symbol)
    {
        $ticker = Ticker::with([
            'documents' => function($q) { $q->latest()->take(10); },
            'financials' => function($q) { $q->latest('year')->latest('quarter')->take(10); },
            'bankMetrics' => function($q) { $q->latest('year')->latest('quarter')->take(10); },
        ])->where('symbol', strtoupper($symbol))->firstOrFail();

        $articles = $ticker->articles()->where('status', 'published')->latest()->take(5)->get();
        $disclosures = $ticker->disclosures()->with('kiBrief')->latest('date')->take(5)->get();

        $realtimePrice = $ticker->current_price ?? rand(1000, 10000);

        $isWatchlisted = false;
        if (auth()->check()) {
            $isWatchlisted = \App\Models\Watchlist::where('user_id', auth()->id())
                ->where('ticker_id', $ticker->id)
                ->exists();
        }

        return Inertia::render('EmitenHub/Show', [
            'ticker' => $ticker,
            'articles' => $articles,
            'disclosures' => $disclosures,
            'realtimePrice' => $realtimePrice,
            'isWatchlisted' => $isWatchlisted,
        ]);
    }
    
    public function tickers()
    {
        $tickers = \Illuminate\Support\Facades\Cache::remember('market_tickers', 300, function () {
            // Include top 10 tickers
            $symbols = ['BBCA.JK', 'BBRI.JK', 'BMRI.JK', 'AMMN.JK', 'BREN.JK', 'TLKM.JK', 'ASII.JK', 'UNTR.JK', 'TPIA.JK', 'INDF.JK'];
            $results = [];
            $apiKey = config('services.sectors.key');
            
            // Get data from 7 days ago to ensure we have at least 2 trading days
            $start = now()->subDays(7)->format('Y-m-d');
            
            // Multi-curl or parallel requests would be faster, but since it's cached every 5 mins,
            // sequential is acceptable. We use Pool for concurrent requests to speed up the cache generation.
            $responses = \Illuminate\Support\Facades\Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($symbols, $apiKey, $start) {
                $reqs = [];
                foreach ($symbols as $symbol) {
                    $reqs[] = $pool->as($symbol)->withHeaders(['Authorization' => $apiKey])->timeout(5)->get("https://api.sectors.app/v2/daily/{$symbol}/?start={$start}");
                }
                return $reqs;
            });
            
            foreach ($responses as $symbol => $response) {
                if ($response->successful()) {
                    $data = $response->json();
                    if (is_array($data) && count($data) >= 2) {
                        $latest = $data[count($data) - 1];
                        $previous = $data[count($data) - 2];
                        
                        $price = $latest['close'];
                        $prevPrice = $previous['close'];
                        
                        $changePercent = 0;
                        if ($prevPrice > 0) {
                            $changePercent = (($price - $prevPrice) / $prevPrice) * 100;
                        }
                        
                        $results[] = [
                            'symbol' => str_replace('.JK', '', $symbol),
                            'price' => number_format($price, 0, ',', '.'),
                            'change' => ($changePercent > 0 ? '+' : '') . number_format($changePercent, 2) . '%',
                            'isUp' => $changePercent >= 0,
                            'category' => 'stock'
                        ];
                    }
                }
            }
            
            return $results;
        });

        return response()->json($tickers);
    }
}
