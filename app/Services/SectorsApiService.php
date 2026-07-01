<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Pool;
use App\Models\ApiSyncLog;
use App\Models\MarketSnapshot;
use Carbon\Carbon;

class SectorsApiService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.sectors.app/v2';

    public function __construct()
    {
        $this->apiKey = config('services.sectors.key', env('SECTOR_API_KEY'));
    }

    protected function headers(): array
    {
        return ['Authorization' => $this->apiKey];
    }

    /**
     * Fetch index daily prices for IHSG, LQ45, IDX30.
     * Returns array of formatted snapshot rows ready for upsert.
     */
    public function fetchIndexSnapshots(): array
    {
        $start = now()->subDays(30)->format('Y-m-d');
        $end   = now()->format('Y-m-d');

        $indices = [
            'ihsg'  => 'IHSG',
            'lq45'  => 'LQ45',
            'idx30' => 'IDX30',
        ];

        $results = [];
        $creditsUsed = 0;
        $startedAt = now();

        try {
            $responses = Http::pool(function (Pool $pool) use ($indices, $start, $end) {
                foreach ($indices as $code => $label) {
                    $pool->as($code)
                        ->withHeaders($this->headers())
                        ->timeout(10)
                        ->get("{$this->baseUrl}/index-daily/{$code}/", [
                            'start' => $start,
                            'end'   => $end,
                        ]);
                }
            });

            foreach ($indices as $code => $label) {
                $response = $responses[$code] ?? null;

                if ($response && $response->successful()) {
                    $data = $response->json();
                    $creditsUsed++;

                    if (!is_array($data) || count($data) < 2) {
                        continue;
                    }

                    // Build sparkline from last 30 data points
                    $sparkline = array_map(fn($r) => $r['price'], $data);

                    $latest   = $data[count($data) - 1];
                    $previous = $data[count($data) - 2];

                    $price     = (float) ($latest['price'] ?? 0);
                    $prevPrice = (float) ($previous['price'] ?? $price);
                    $changeAbs = $price - $prevPrice;
                    $changePct = $prevPrice > 0 ? (($price - $prevPrice) / $prevPrice) * 100 : 0;

                    // YTD: hitung dari harga awal tahun jika ada
                    $ytdPct = null;
                    $currentYear = now()->year;
                    $ytdEntry = collect($data)->first(
                        fn($r) => str_starts_with($r['date'], (string) $currentYear)
                    );
                    if ($ytdEntry) {
                        $ytdBase = (float) $ytdEntry['price'];
                        $ytdPct  = $ytdBase > 0 ? (($price - $ytdBase) / $ytdBase) * 100 : null;
                    }

                    $results[] = [
                        'date'             => $latest['date'],
                        'symbol_or_metric' => $label,
                        'value'            => $price,
                        'change_abs'       => $changeAbs,
                        'change_pct'       => $changePct,
                        'ytd_pct'          => $ytdPct,
                        'sparkline_json'   => $sparkline, // raw array — model cast handles json_encode
                        'source'           => 'sectors_api',
                        'last_sync'        => now(),
                    ];
                } else {
                    Log::warning("SectorsApiService: failed to fetch index [{$code}]", [
                        'status' => $response?->status(),
                        'body'   => $response?->body(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('SectorsApiService::fetchIndexSnapshots exception: ' . $e->getMessage());
            $this->logSync('index_daily', 'failed', $creditsUsed, $startedAt, $e->getMessage());
            return [];
        }

        $this->logSync('index_daily', 'success', $creditsUsed, $startedAt);

        return $results;
    }

    public function fetchSectorIndices(): array
    {
        $start = now()->subDays(2)->format('Y-m-d');
        $end   = now()->format('Y-m-d');

        // Map IDX sector codes to human readable names expected by DeskBrief
        $indices = [
            'idxfinance' => 'Banking',
            'idxtechno'  => 'Technology',
            'idxhealth'  => 'Healthcare',
            'idxprop'    => 'Property',
            'idxenergy'  => 'Energy',
            'idxbasic'   => 'Basic Materials',
            'idxcycli'   => 'Cons. Discretionary',
            'idxnoncyc'  => 'Cons. Staples',
            'idxtrans'   => 'Transportation',
            'idxindust'  => 'Industrial',
            'idxinfra'   => 'Infrastructure',
        ];

        $results = [];
        $creditsUsed = 0;
        $startedAt = now();

        try {
            $responses = Http::pool(function (Pool $pool) use ($indices, $start, $end) {
                foreach (array_keys($indices) as $code) {
                    $pool->as($code)
                        ->withHeaders($this->headers())
                        ->timeout(10)
                        ->get("{$this->baseUrl}/index-daily/{$code}/", [
                            'start' => $start,
                            'end'   => $end,
                        ]);
                }
            });

            foreach ($indices as $code => $label) {
                $response = $responses[$code] ?? null;

                if ($response && $response->successful()) {
                    $data = $response->json();
                    $creditsUsed++;

                    if (!is_array($data) || count($data) < 2) {
                        continue;
                    }

                    $latest   = $data[count($data) - 1];
                    $previous = $data[count($data) - 2];

                    $price     = (float) ($latest['price'] ?? 0);
                    $prevPrice = (float) ($previous['price'] ?? $price);
                    $changePct = $prevPrice > 0 ? (($price - $prevPrice) / $prevPrice) * 100 : 0;

                    $results[] = [
                        'date'   => $latest['date'],
                        'sector' => $label,
                        'return_1d' => round($changePct, 2),
                    ];
                } else {
                    Log::warning("SectorsApiService: failed to fetch sector index [{$code}]", [
                        'status' => $response?->status(),
                        'body'   => $response?->body(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('SectorsApiService::fetchSectorIndices exception: ' . $e->getMessage());
            $this->logSync('sector_daily', 'failed', $creditsUsed, $startedAt, $e->getMessage());
            return [];
        }

        $this->logSync('sector_daily', 'success', $creditsUsed, $startedAt);

        return $results;
    }

    /**
     * Fetch top gainers & losers for today (1d period).
     * Returns ['gainers' => [...], 'losers' => [...]]
     */
    public function fetchTopMovers(): array
    {
        $startedAt = now();

        try {
            // Endpoint: GET /v2/companies/top-changes/
            // Response structure: { top_gainers: { 1d: [...] }, top_losers: { 1d: [...] } }
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->get("{$this->baseUrl}/companies/top-changes/", [
                    'classifications' => 'top_gainers,top_losers',
                ]);

            if ($response->successful()) {
                $this->logSync('top_changes', 'success', 1, $startedAt);
                $data = $response->json();

                // Parse nested structure: { top_gainers: { 1d: [{symbol, price_change, ...}] } }
                $gainers = $data['top_gainers']['1d'] ?? [];
                $losers  = $data['top_losers']['1d']  ?? [];

                // Normalize field: price_change is decimal (0.05 = 5%), convert to pct
                $normalize = function (array $items): array {
                    return array_map(function ($item) {
                        return [
                            'symbol'     => str_replace('.JK', '', $item['symbol'] ?? ''),
                            'name'       => $item['name'] ?? '',
                            'price_pct'  => round(($item['price_change'] ?? 0) * 100, 2),
                            'last_close' => $item['last_close_price'] ?? null,
                        ];
                    }, $items);
                };

                return [
                    'gainers' => array_slice($normalize($gainers), 0, 5),
                    'losers'  => array_slice($normalize($losers),  0, 5),
                ];
            }

            $this->logSync('top_changes', 'failed', 0, $startedAt, $response->body());
        } catch (\Throwable $e) {
            Log::error('SectorsApiService::fetchTopMovers exception: ' . $e->getMessage());
            $this->logSync('top_changes', 'failed', 0, $startedAt, $e->getMessage());
        }

        return ['gainers' => [], 'losers' => []];
    }

    /**
     * Fetch most traded stocks for today.
     */
    public function fetchMostTraded(): array
    {
        $startedAt = now();
        $today     = now()->format('Y-m-d');

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->get("{$this->baseUrl}/most-traded/", [
                    'start' => $today,
                    'end'   => $today,
                ]);

            if ($response->successful()) {
                $this->logSync('most_traded', 'success', 1, $startedAt);
                $data = $response->json();

                // API returns keyed by date
                $dayData = $data[$today] ?? (is_array($data) ? array_values($data)[0] ?? [] : []);
                return array_slice($dayData, 0, 10);
            }

            $this->logSync('most_traded', 'failed', 0, $startedAt, $response->body());
        } catch (\Throwable $e) {
            Log::error('SectorsApiService::fetchMostTraded exception: ' . $e->getMessage());
            $this->logSync('most_traded', 'failed', 0, $startedAt, $e->getMessage());
        }

        return [];
    }

    /**
     * Persist index snapshots to market_snapshots table.
     */
    public function persistIndexSnapshots(array $snapshots): void
    {
        foreach ($snapshots as $snap) {
            MarketSnapshot::updateOrCreate(
                [
                    'date'             => $snap['date'],
                    'symbol_or_metric' => $snap['symbol_or_metric'],
                ],
                [
                    'value'          => $snap['value'],
                    'change_abs'     => $snap['change_abs'],
                    'change_pct'     => $snap['change_pct'],
                    'sparkline_json' => $snap['sparkline_json'],
                    'source'         => $snap['source'],
                    'last_sync'      => $snap['last_sync'],
                ]
            );
        }
    }

    /**
     * Log API call to api_sync_logs table.
     */
    protected function logSync(
        string $endpoint,
        string $status,
        int $credits,
        Carbon $startedAt,
        ?string $error = null
    ): void {
        try {
            ApiSyncLog::create([
                'provider'      => 'sectors_app',
                'endpoint'      => $endpoint,
                'status'        => $status,
                'credits_used'  => $credits,
                'started_at'    => $startedAt,
                'completed_at'  => now(),
                'error_message' => $error,
            ]);
        } catch (\Throwable $e) {
            Log::error('SectorsApiService::logSync failed: ' . $e->getMessage());
        }
    }
}
