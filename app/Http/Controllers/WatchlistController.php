<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Ticker;
use App\Models\Watchlist;

class WatchlistController extends Controller
{
    public function index()
    {
        $watchlists = auth()->user()->watchlists()->with('ticker')->latest()->get();
        return Inertia::render('Watchlist/Index', [
            'watchlists' => $watchlists
        ]);
    }

    public function toggle(Request $request, $tickerId)
    {
        $user = auth()->user();
        $ticker = Ticker::findOrFail($tickerId);

        $exists = Watchlist::where('user_id', $user->id)->where('ticker_id', $ticker->id)->first();

        if ($exists) {
            $exists->delete();
            return back()->with('success', $ticker->symbol . ' dihapus dari Watchlist.');
        } else {
            Watchlist::create([
                'user_id' => $user->id,
                'ticker_id' => $ticker->id,
            ]);
            return back()->with('success', $ticker->symbol . ' ditambahkan ke Watchlist.');
        }
    }
}
