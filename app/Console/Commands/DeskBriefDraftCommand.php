<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeskBrief;
use Carbon\Carbon;

class DeskBriefDraftCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deskbrief:draft {date? : The date to generate Desk Brief for (Y-m-d). Defaults to today.} {--force : Overwrite if already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and auto-publish a new Desk Brief for the specified date';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\MarketIntelligence\ScoringEngine $scoringEngine, \App\Services\MarketIntelligence\DeltaEngine $deltaEngine, \App\Services\MarketIntelligence\KeyDriversEngine $keyDriversEngine)
    {
        $dateParam = $this->argument('date');
        $date = $dateParam ? Carbon::parse($dateParam)->toDateString() : today()->toDateString();

        $this->info("[deskbrief:draft] Checking data for date: {$date}");

        $existing = DeskBrief::whereDate('date', $date)->first();

        if ($existing) {
            if ($this->option('force')) {
                $this->info("  ! A Desk Brief for {$date} already exists (ID: {$existing->id}). Force deleting...");
                $existing->delete();
            } else {
                $this->warn("  ✗ A Desk Brief for {$date} already exists (ID: {$existing->id}). Skipping creation. Use --force to overwrite.");
                return Command::SUCCESS;
            }
        }

        // Validate if data from Sectors is actually available for this date
        $latestDate = \App\Models\MarketSnapshot::whereDate('date', '<=', $date)
            ->where('symbol_or_metric', 'IHSG')
            ->max('date');
            
        if ($latestDate !== $date && !$this->option('force')) {
            $this->warn("  ✗ Data from Sectors for {$date} is not yet available (Latest is {$latestDate}). Aborting draft creation.");
            $this->info("  Hint: The cronjob will try again later if configured, or you can run this manually when data is ready.");
            return Command::FAILURE;
        }

        $this->info("  → Calculating Key Drivers...");
        
        $usdIdrProxy = \App\Models\MarketSnapshot::where('date', '<=', $date)
            ->where('symbol_or_metric', 'USD_IDR_PROXY')
            ->orderBy('date', 'desc')
            ->first();

        $manualInputs = [
            'RUPIAH_BI_SBN_YIELD' => [
                'usd_idr_change_5d' => $usdIdrProxy ? (float) $usdIdrProxy->change_pct : null,
                'sbn_10y' => null,
                'sbn_10y_change_5d' => null,
                'bi_stance' => 'neutral',
            ]
        ];

        $driversData = $keyDriversEngine->buildIhsgKeyDrivers('LQ45', 5, $date, $manualInputs);

        $this->info("  → Calculating Regime Score...");
        $stance = $scoringEngine->calculateRegimeScore($date, $driversData);

        $this->info("  → Calculating Confluence...");
        $scoringEngine->calculateConfluence($date);

        $this->info("  → Calculating Delta (What Changed)...");
        $delta = $deltaEngine->getWhatChanged($date);
        
        // You would typically save $delta array as JSON to a 'what_changed_json' column in desk_briefs, 
        // but since we haven't added it to schema yet, we just generate it to ensure the engine works.

        $this->info("  → Creating Desk Brief draft...");

        $brief = DeskBrief::create([
            'date' => $date,
            'session_type' => 'EOD',
            'market_stance_id' => $stance->id,
            'status' => 'published', // Client request: Auto publish
            'published_at' => now(),
            'title' => 'Desk Brief - ' . Carbon::parse($date)->format('d M Y'),
            'market_read' => '',
            'so_what' => '',
            'what_to_do' => '',
        ]);

        $this->info("  ✓ Desk Brief draft created successfully! (ID: {$brief->id})");

        $this->info("  → Saving Key Drivers...");
        
        foreach ($driversData as $driverData) {
            $brief->drivers()->create([
                'rank' => $driverData['rank'],
                'title' => $driverData['title'],
                'category' => $driverData['category'],
                'source' => $driverData['components']['data_quality'] ?? 'unknown',
                'impact_level' => $driverData['impact_level'],
                'explanation' => $driverData['explanation'],
                'affected_sectors_json' => $driverData['components'] ?? [],
            ]);
        }
        $this->info("  ✓ Key Drivers saved successfully!");

        $this->info("  ⚠ Please review and publish via Admin Panel.");
        $this->info("[deskbrief:draft] Done.");

        return Command::SUCCESS;
    }
}
