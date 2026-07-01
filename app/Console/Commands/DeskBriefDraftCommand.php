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
    protected $signature = 'deskbrief:draft {date? : The date to generate Desk Brief for (Y-m-d). Defaults to today.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and auto-publish a new Desk Brief for the specified date';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\MarketIntelligence\ScoringEngine $scoringEngine, \App\Services\MarketIntelligence\DeltaEngine $deltaEngine)
    {
        $dateParam = $this->argument('date');
        $date = $dateParam ? Carbon::parse($dateParam)->toDateString() : today()->toDateString();

        $this->info("[deskbrief:draft] Checking data for date: {$date}");

        $existing = DeskBrief::whereDate('date', $date)->first();

        if ($existing) {
            $this->warn("  ✗ A Desk Brief for {$date} already exists (ID: {$existing->id}). Skipping creation.");
            return Command::SUCCESS;
        }

        $this->info("  → Calculating Regime Score...");
        $stance = $scoringEngine->calculateRegimeScore($date);

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
        $this->info("  ⚠ Please review and publish via Admin Panel.");
        $this->info("[deskbrief:draft] Done.");

        return Command::SUCCESS;
    }
}
