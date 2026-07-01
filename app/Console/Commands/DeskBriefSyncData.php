<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MarketIntelligence\DataSyncService;

class DeskBriefSyncData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deskbrief:sync {date? : The date to sync data for (Y-m-d). Defaults to today.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync EOD market data from Sectors.app and Macro sources (FRED, WB, etc) to cache tables';

    /**
     * Execute the console command.
     */
    public function handle(DataSyncService $syncService)
    {
        $date = $this->argument('date') ?? today()->toDateString();

        $this->info("[deskbrief:sync] Starting data sync for {$date}...");

        try {
            $syncService->syncAll($date);
            $this->info("  ✓ Data sync completed successfully.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("  ✗ Data sync failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
