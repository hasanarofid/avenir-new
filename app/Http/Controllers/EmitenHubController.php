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
            $query->where('symbol', 'like', '%' . $request->search . '%')
                  ->orWhere('company_name', 'like', '%' . $request->search . '%');
        }

        $tickers = $query->paginate(24);

        return Inertia::render('EmitenHub/Index', [
            'tickers' => $tickers,
            'filters' => $request->only('search')
        ]);
    }

    public function show($symbol)
    {
        $ticker = Ticker::where('symbol', strtoupper($symbol))->firstOrFail();
        
        // Load related articles or research for this ticker
        $articles = $ticker->articles()->where('status', 'published')->latest()->take(5)->get();

        // Load KI Briefs / Disclosures for this ticker
        $disclosures = $ticker->disclosures()->with('kiBrief')->latest('date')->take(5)->get();

        // Nanti diganti dengan API Pihak Ketiga (seperti GoAPI/Yahoo Finance)
        // Untuk MVP, pakai data dummy atau data yang ada di tabel.
        $realtimePrice = $ticker->current_price ?? rand(1000, 10000); // Dummy for MVP

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
