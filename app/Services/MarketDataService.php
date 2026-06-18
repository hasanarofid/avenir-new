<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MarketDataService
{
    protected $apiKey;
    protected $apiHost;

    public function __construct()
    {
        $this->apiKey = env('RAPIDAPI_KEY');
        $this->apiHost = env('RAPIDAPI_HOST', 'yahoo-finance166.p.rapidapi.com');
    }

    /**
     * Fetch quotes and intraday chart data for a given list of symbols.
     * 
     * @param array $symbols Example: ['BBRI.JK', '^JKSE', 'IDR=X']
     * @return array
     */
    public function getQuotes(array $symbols)
    {
        $results = [];
        
        try {
            // Using Http::pool to fetch all symbols concurrently from Yahoo Finance Public API
            $responses = \Illuminate\Support\Facades\Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($symbols) {
                foreach ($symbols as $sym) {
                    $pool->as($sym)->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    ])->get("https://query2.finance.yahoo.com/v8/finance/chart/" . urlencode($sym));
                }
            });

            foreach ($responses as $sym => $response) {
                if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                    $data = $response->json();
                    $meta = $data['chart']['result'][0]['meta'] ?? null;
                    if ($meta) {
                        $price = $meta['regularMarketPrice'] ?? 0;
                        $prevClose = $meta['chartPreviousClose'] ?? $price;
                        $change = $price - $prevClose;
                        $changePercent = $prevClose != 0 ? ($change / $prevClose) * 100 : 0;
                        
                        // Extract intraday data points
                        $closes = $data['chart']['result'][0]['indicators']['quote'][0]['close'] ?? [];
                        
                        $chartPoints = [];
                        foreach ($closes as $c) {
                            if ($c !== null) {
                                $chartPoints[] = round($c, 2);
                            }
                        }
                        
                        // Subsample to max ~60 points to keep payload small
                        $subsampled = [];
                        if (count($chartPoints) > 0) {
                            $step = max(1, floor(count($chartPoints) / 60));
                            for ($i = 0; $i < count($chartPoints); $i += $step) {
                                $subsampled[] = $chartPoints[$i];
                            }
                        }
                        
                        $results[$sym] = [
                            'symbol' => $sym,
                            'price' => $price,
                            'prevClose' => $prevClose,
                            'change' => round($change, 2),
                            'changePercent' => round($changePercent, 2),
                            'name' => $meta['shortName'] ?? str_replace('.JK', '', $sym),
                            'chartData' => $subsampled,
                        ];
                        continue;
                    }
                }
                
                // Static fallback if specific symbol fails
                $results[$sym] = [
                    'symbol' => $sym,
                    'price' => 0,
                    'prevClose' => 0,
                    'change' => 0,
                    'changePercent' => 0,
                    'name' => str_replace('.JK', '', $sym),
                    'chartData' => [],
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception in Yahoo Finance Public API: ' . $e->getMessage());
            // Hardcode base defaults if everything fails
            foreach ($symbols as $sym) {
                $results[$sym] = [
                    'symbol' => $sym,
                    'price' => 0,
                    'prevClose' => 0,
                    'change' => 0,
                    'changePercent' => 0,
                    'name' => str_replace('.JK', '', $sym),
                    'chartData' => [],
                ];
            }
        }

        // Apply specific naming overrides for market summary
        if (isset($results['^JKSE'])) $results['^JKSE']['name'] = 'IHSG';
        if (isset($results['IDR=X'])) $results['IDR=X']['name'] = 'USD/IDR';
        if (isset($results['GC=F'])) $results['GC=F']['name'] = 'Gold';
        if (isset($results['BZ=F'])) $results['BZ=F']['name'] = 'Oil';

        return $results;
    }
}
