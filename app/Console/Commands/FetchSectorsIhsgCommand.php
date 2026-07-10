<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\MarketSnapshot;
use Carbon\Carbon;

#[Signature('sectors:fetch-ihsg')]
#[Description('Fetch the latest IHSG closing price from Sectors API')]
class FetchSectorsIhsgCommand extends Command
{
    public function handle()
    {
        $this->info("Fetching IHSG data from Sectors.app...");

        $apiKey = env('SECTOR_API_KEY');
        if (empty($apiKey)) {
            $this->error("SECTOR_API_KEY is not defined in .env");
            return Command::FAILURE;
        }

        // We fetch the last 14 days to ensure we get the latest trading day (accounting for long holidays)
        $start = today()->subDays(14)->format('Y-m-d');
        $end = today()->format('Y-m-d');
        
        $url = "https://api.sectors.app/v2/index-daily/ihsg/?start={$start}&end={$end}";
        
        $response = Http::withHeaders([
            'Authorization' => $apiKey
        ])->get($url);

        if (!$response->successful()) {
            $this->error("Failed to fetch data. Status: " . $response->status());
            $this->error($response->body());
            return Command::FAILURE;
        }

        $data = $response->json();
        
        if (!is_array($data) || empty($data)) {
            $this->warn("No data returned from API for the given date range.");
            return Command::SUCCESS;
        }

        // Get the latest item in the array
        $latestRecord = end($data);
        $date = Carbon::parse($latestRecord['date'])->format('Y-m-d');
        $price = $latestRecord['price'];

        $this->info("Latest IHSG Data: Date={$date}, Price={$price}");

        // Save to market_snapshots
        MarketSnapshot::updateOrCreate(
            ['date' => $date, 'symbol_or_metric' => 'IHSG'],
            [
                'value' => $price,
                'source' => 'sectors_api'
            ]
        );

        $this->info("Saved to market_snapshots successfully.");
        return Command::SUCCESS;
    }
}
