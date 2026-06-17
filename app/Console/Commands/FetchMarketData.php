<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Services\MarketDataService;
use Illuminate\Support\Facades\Cache;

class FetchMarketData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'market:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and cache market data from Yahoo Finance API';

    /**
     * Execute the console command.
     */
    public function handle(MarketDataService $marketService)
    {
        $this->info('Starting market data fetch...');

        // Base/Default tickers (Market Summary)
        $symbols = ['^JKSE', 'IDR=X', 'GC=F', 'BZ=F'];

        // Get admin settings
        $settings = Setting::pluck('value', 'key');
        $topTickersStr = $settings['market_top_tickers'] ?? 'BBRI.JK, TLKM.JK, ASII.JK, AMMN.JK, MDKA.JK';
        $watchlistStr = $settings['market_watchlist_tickers'] ?? 'BBRI.JK, TLKM.JK, ASII.JK, MDKA.JK';
        $trendingStr = $settings['market_trending_tickers'] ?? 'BBRI.JK, TLKM.JK, ASII.JK, MDKA.JK, AMMN.JK, GOTO.JK';

        $topTickers = array_map('trim', explode(',', $topTickersStr));
        $watchlist = array_map('trim', explode(',', $watchlistStr));
        $trending = array_map('trim', explode(',', $trendingStr));

        // Merge all unique tickers
        $allSymbols = array_unique(array_merge($symbols, $topTickers, $watchlist, $trending));
        
        // Remove empty strings
        $allSymbols = array_filter($allSymbols);

        $this->info('Fetching data for ' . count($allSymbols) . ' symbols...');

        $data = $marketService->getQuotes($allSymbols);

        // Cache the data for 1 minute
        Cache::put('market_summary', $data, now()->addMinute(1));

        $this->info('Successfully fetched and cached market data.');
    }
}
