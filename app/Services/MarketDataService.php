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
     * Fallback mock data if API fails or is unconfigured.
     */
    protected function getMockData($symbols)
    {
        $mock = [];
        foreach ($symbols as $sym) {
            $isPositive = rand(0, 1) === 1;
            $price = rand(1000, 10000);
            $change = rand(10, 100) * ($isPositive ? 1 : -1);
            $changePct = round(($change / $price) * 100, 2);

            $mock[$sym] = [
                'symbol' => $sym,
                'price' => $price,
                'change' => $change,
                'changePercent' => $changePct,
                'name' => str_replace('.JK', '', $sym)
            ];
        }

        // Hardcode indices for better mock display
        if (in_array('^JKSE', $symbols)) {
            $mock['^JKSE'] = ['symbol' => '^JKSE', 'price' => 7250.15, 'change' => 25.50, 'changePercent' => 0.35, 'name' => 'IHSG'];
        }
        if (in_array('IDR=X', $symbols)) {
            $mock['IDR=X'] = ['symbol' => 'IDR=X', 'price' => 15500.00, 'change' => -50.00, 'changePercent' => -0.32, 'name' => 'USD/IDR'];
        }
        if (in_array('GC=F', $symbols)) {
            $mock['GC=F'] = ['symbol' => 'GC=F', 'price' => 2450.80, 'change' => 15.20, 'changePercent' => 0.62, 'name' => 'Gold'];
        }
        if (in_array('BZ=F', $symbols)) {
            $mock['BZ=F'] = ['symbol' => 'BZ=F', 'price' => 82.50, 'change' => -1.10, 'changePercent' => -1.31, 'name' => 'Oil'];
        }

        return $mock;
    }
}
