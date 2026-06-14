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
            ])
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
}
