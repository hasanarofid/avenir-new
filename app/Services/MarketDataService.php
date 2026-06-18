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
     * Fetch quotes for a given list of symbols.
     * 
     * @param array $symbols Example: ['BBRI.JK', '^JKSE', 'IDR=X']
     * @return array
     */
    public function getQuotes(array $symbols)
    {
        if (empty($this->apiKey)) {
            Log::warning('RapidAPI Key is not configured for Yahoo Finance.');
            return $this->getMockData($symbols);
        }

        $symbolsStr = implode(',', $symbols);

        try {
            // NOTE: The exact endpoint depends on the specific RapidAPI provider format.
            // Using a common format here. If you get a 404, adjust the endpoint path.
            $response = Http::withHeaders([
                'x-rapidapi-host' => $this->apiHost,
                'x-rapidapi-key'  => $this->apiKey,
            ])->get("https://{$this->apiHost}/api/market/get-quote", [
                'region' => 'US',
                'symbols' => $symbolsStr
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->formatData($data);
            }

            Log::error('Yahoo Finance API error', ['status' => $response->status(), 'body' => $response->body()]);
            return $this->getMockData($symbols);

        } catch (\Exception $e) {
            Log::error('Exception calling Yahoo Finance API: ' . $e->getMessage());
            return $this->getMockData($symbols);
        }
    }

    protected function formatData($apiResponse)
    {
        $results = [];
        
        // RapidAPI responses from Yahoo Finance typically wrap results
        // like `quoteResponse.result` or sometimes just an array under `quote`.
        $quotes = $apiResponse['quoteResponse']['result'] ?? $apiResponse['quote'] ?? []; 
        
        // If it directly returned an array of objects
        if (empty($quotes) && is_array($apiResponse) && isset($apiResponse[0]['symbol'])) {
            $quotes = $apiResponse;
        }

        foreach ($quotes as $quote) {
            $symbol = $quote['symbol'] ?? '';
            if ($symbol) {
                $results[$symbol] = [
                    'symbol' => $symbol,
                    'price' => $quote['regularMarketPrice'] ?? 0,
                    'change' => $quote['regularMarketChange'] ?? 0,
                    'changePercent' => $quote['regularMarketChangePercent'] ?? 0,
                    'name' => $quote['shortName'] ?? $quote['longName'] ?? $symbol,
                ];
            }
        }
        return $results;
    }

    /**
     * Fallback to free Yahoo Finance API if RapidAPI fails or is unconfigured.
     */
    protected function getMockData($symbols)
    {
        $results = [];
        
        try {
            // Using Http::pool to fetch all symbols concurrently
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
                        
                        $results[$sym] = [
                            'symbol' => $sym,
                            'price' => $price,
                            'change' => round($change, 2),
                            'changePercent' => round($changePercent, 2),
                            'name' => $meta['shortName'] ?? str_replace('.JK', '', $sym),
                        ];
                        continue;
                    }
                }
                
                // Static fallback if specific symbol fails
                $results[$sym] = [
                    'symbol' => $sym,
                    'price' => 0,
                    'change' => 0,
                    'changePercent' => 0,
                    'name' => str_replace('.JK', '', $sym),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception in Yahoo Finance Public API fallback: ' . $e->getMessage());
            // Hardcode base defaults if everything fails
            foreach ($symbols as $sym) {
                $results[$sym] = [
                    'symbol' => $sym,
                    'price' => 0,
                    'change' => 0,
                    'changePercent' => 0,
                    'name' => str_replace('.JK', '', $sym),
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
