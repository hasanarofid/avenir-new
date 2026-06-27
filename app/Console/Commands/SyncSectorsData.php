<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Services\SectorsApiService;

class SyncSectorsData extends Command
{
    protected $signature = 'sectors:sync {--force : Bypass cache and force fresh fetch}';

    protected $description = 'Sync market data from Sectors.app API and persist to DB';

    public function handle(SectorsApiService $sectors): int
    {
        $this->info('[sectors:sync] Starting Sectors API data sync...');

        // 1. Fetch & persist index snapshots (IHSG, LQ45, IDX30)
        $this->info('  → Fetching index snapshots...');
        $indexSnapshots = $sectors->fetchIndexSnapshots();

        if (!empty($indexSnapshots)) {
            $sectors->persistIndexSnapshots($indexSnapshots);
            $this->info('  ✓ Persisted ' . count($indexSnapshots) . ' index snapshot(s).');
        } else {
            $this->warn('  ✗ No index snapshot data returned.');
        }

        // 2. Fetch top movers and cache (TTL sampai end of day WIB)
        $this->info('  → Fetching top movers...');
        $movers = $sectors->fetchTopMovers();
        Cache::put('sectors_top_movers', $movers, now()->endOfDay()->addHours(7)); // +7h offset WIB
        $gCount = count($movers['gainers']);
        $lCount = count($movers['losers']);
        $this->info("  ✓ Cached top movers: {$gCount} gainers, {$lCount} losers.");

        // 3. Fetch most traded and cache (TTL sama)
        $this->info('  → Fetching most traded stocks...');
        $mostTraded = $sectors->fetchMostTraded();
        Cache::put('sectors_most_traded', $mostTraded, now()->endOfDay()->addHours(7));
        $this->info('  ✓ Cached most traded: ' . count($mostTraded) . ' stock(s).');


        $this->info('[sectors:sync] Done.');

        return Command::SUCCESS;
    }
}
