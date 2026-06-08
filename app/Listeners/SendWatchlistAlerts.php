<?php

namespace App\Listeners;

use App\Events\ArticlePublished;
use App\Events\KIBriefPublished;
use App\Models\Watchlist;
use Illuminate\Support\Facades\Log;

class SendWatchlistAlerts
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $ticker = null;

        if ($event instanceof ArticlePublished) {
            // Get the first ticker associated with the article (for MVP)
            $ticker = $event->article->tickers()->first();
        } elseif ($event instanceof KIBriefPublished) {
            $ticker = $event->kiBrief->disclosure->ticker;
        }

        if ($ticker) {
            // Find all users who have this ticker in their watchlist
            $watchlists = Watchlist::where('ticker_id', $ticker->id)->get();

            foreach ($watchlists as $watchlist) {
                // For now, just log it as the foundation for future alerts
                Log::info('Watchlist alert triggered for user ' . $watchlist->user_id . ' for ticker ' . $ticker->symbol);
            }
        }
    }
}
