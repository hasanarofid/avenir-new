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
    public function handle()
    {
        $dateParam = $this->argument('date');
        $date = $dateParam ? Carbon::parse($dateParam)->toDateString() : today()->toDateString();

        $this->info("[deskbrief:draft] Checking data for date: {$date}");

        $existing = DeskBrief::whereDate('date', $date)->first();

        if ($existing) {
            $this->warn("  ✗ A Desk Brief for {$date} already exists (ID: {$existing->id}). Skipping creation.");
            return Command::SUCCESS;
        }

        $this->info("  → Creating and auto-publishing new Desk Brief...");

        $brief = DeskBrief::create([
            'date' => $date,
            'session_type' => 'EOD',
            'status' => 'published',
            'published_at' => now(),
            'title' => 'Desk Brief - ' . Carbon::parse($date)->format('d M Y'),
            'market_read' => '',
            'so_what' => '',
            'what_to_do' => '',
        ]);

        $this->info("  ✓ Desk Brief created and published successfully! (ID: {$brief->id})");
        $this->info("[deskbrief:draft] Done.");

        return Command::SUCCESS;
    }
}
