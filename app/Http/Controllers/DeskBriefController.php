<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\DeskBrief;
use App\Models\MarketSnapshot;
use App\Models\ApiSyncLog;
use Illuminate\Support\Facades\Cache;

class DeskBriefController extends Controller
{
    public function index()
    {
        $latestBrief = DeskBrief::with(['marketStance', 'drivers', 'radarStocks'])
            ->where('status', 'published')
            ->orderBy('date', 'desc')
            ->first();

        return Inertia::render('DeskBrief/Index', [
            'deskBrief'    => $latestBrief,
            'snapshots'    => $this->getSnapshots(),
            'topMovers'    => $this->getTopMovers(),
            'mostTraded'   => $this->getMostTraded(),
            'apiStatus'    => $this->getApiStatus(),
        ]);
    }

    /**
     * Build snapshot cards from DB (market_snapshots), padded with static fallbacks.
     */
    private function getSnapshots(): array
    {
        // Read from DB — latest record per symbol
        $dbSnaps = MarketSnapshot::whereIn('symbol_or_metric', ['IHSG', 'LQ45', 'IDX30'])
            ->orderBy('date', 'desc')
            ->get()
            ->unique('symbol_or_metric')
            ->keyBy('symbol_or_metric');

        $formatted = [];

        foreach (['IHSG', 'LQ45', 'IDX30'] as $sym) {
            $snap = $dbSnaps->get($sym);

            if ($snap) {
                $pct      = (float) $snap->change_pct;
                $abs      = (float) $snap->change_abs;
                $value    = (float) $snap->value;
                $ytdPct   = null; // YTD bisa di-extend nanti

                // Format value sesuai range (IHSG ribuan, LQ45/IDX30 ratusan)
                $formatted[] = [
                    'symbol'    => $sym,
                    'value'     => number_format($value, 2, '.', ','),
                    'change'    => ($pct >= 0 ? '+' : '') . number_format($pct, 2) . '%',
                    'changeAbs' => ($abs >= 0 ? '+' : '') . number_format($abs, 2),
                    'isUp'      => $pct >= 0,
                    'ytd'       => '—',
                    'sparkline' => $snap->sparkline_json ?? [],
                    'lastSync'  => $snap->last_sync?->format('d M Y H:i') . ' WIB',
                    'isLive'    => true,
                    'date'      => $snap->date?->format('Y-m-d'),
                ];
            }
        }

        // Append static fallback cards (non-Sectors data: FX, Yield, Commodity)
        $staticCards = [
            ['symbol' => 'USD/IDR',      'value' => '—', 'change' => '—', 'changeAbs' => '—', 'isUp' => null, 'ytd' => '—', 'sparkline' => [], 'lastSync' => null, 'isLive' => false, 'date' => null],
            ['symbol' => '10Y IND YIELD','value' => '—', 'change' => '—', 'changeAbs' => '—', 'isUp' => null, 'ytd' => '—', 'sparkline' => [], 'lastSync' => null, 'isLive' => false, 'date' => null],
            ['symbol' => 'BRENT',         'value' => '—', 'change' => '—', 'changeAbs' => '—', 'isUp' => null, 'ytd' => '—', 'sparkline' => [], 'lastSync' => null, 'isLive' => false, 'date' => null],
            ['symbol' => 'GOLD',          'value' => '—', 'change' => '—', 'changeAbs' => '—', 'isUp' => null, 'ytd' => '—', 'sparkline' => [], 'lastSync' => null, 'isLive' => false, 'date' => null],
        ];

        return array_merge($formatted, $staticCards);
    }

    /**
     * Top gainers & losers from cache (populated by sectors:sync command).
     */
    private function getTopMovers(): array
    {
        return Cache::get('sectors_top_movers', ['gainers' => [], 'losers' => []]);
    }

    /**
     * Most traded stocks from cache (populated by sectors:sync command).
     */
    private function getMostTraded(): array
    {
        return Cache::get('sectors_most_traded', []);
    }

    /**
     * Build API status card for the data source indicator.
     */
    private function getApiStatus(): array
    {
        // Last successful sync
        $lastSync = ApiSyncLog::where('provider', 'sectors_app')
            ->where('status', 'success')
            ->latest('completed_at')
            ->first();

        // Count today's credits used
        $creditsToday = ApiSyncLog::where('provider', 'sectors_app')
            ->whereDate('completed_at', today())
            ->sum('credits_used');

        // Latest snapshot date in DB
        $latestSnap = MarketSnapshot::where('source', 'sectors_api')
            ->orderBy('date', 'desc')
            ->value('date');

        $isStale = false;
        if ($lastSync) {
            // Stale if last sync > 26 hours ago (accounts for weekends/holidays)
            $isStale = $lastSync->completed_at?->diffInHours(now()) > 26;
        }

        return [
            'provider'     => 'Sectors.app',
            'lastSync'     => $lastSync?->completed_at?->format('d M Y H:i') . ' WIB',
            'creditsToday' => (int) $creditsToday,
            'latestDate'   => $latestSnap,
            'status'       => match(true) {
                !$lastSync            => 'no_data',
                $isStale              => 'stale',
                default               => 'fresh',
            },
        ];
    }
}
