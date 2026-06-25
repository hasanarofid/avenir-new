<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\DeskBrief;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;

class DeskBriefController extends Controller
{
    public function index()
    {
        $latestBrief = DeskBrief::with(['marketStance', 'drivers', 'radarStocks'])
            ->where('status', 'published')
            ->orderBy('date', 'desc')
            ->first();

        // Fetch realtime snapshots from Sectors API
        $snapshots = Cache::remember('desk_brief_snapshots', 300, function () {
            $apiKey = env('SECTOR_API_KEY');
            $start = now()->subDays(7)->format('Y-m-d');
            $results = [];
            
            $indices = ['ihsg' => 'IHSG', 'lq45' => 'LQ45', 'idx30' => 'IDX30'];
            $responses = Http::pool(function (Pool $pool) use ($indices, $apiKey, $start) {
                $reqs = [];
                foreach ($indices as $code => $label) {
                    $reqs[] = $pool->as($code)->withHeaders(['Authorization' => $apiKey])->timeout(5)->get("https://api.sectors.app/v2/index-daily/{$code}/?start={$start}");
                }
                return $reqs;
            });
            
            foreach ($indices as $code => $label) {
                if (isset($responses[$code]) && $responses[$code]->successful()) {
                    $data = $responses[$code]->json();
                    if (is_array($data) && count($data) >= 2) {
                        $latest = $data[count($data) - 1];
                        $previous = $data[count($data) - 2];
                        $price = $latest['price'] ?? 0;
                        $prevPrice = $previous['price'] ?? 0;
                        
                        $changePercent = 0;
                        if ($prevPrice > 0) {
                            $changePercent = (($price - $prevPrice) / $prevPrice) * 100;
                        }
                        
                        $results[] = [
                            'symbol' => $label,
                            'value' => number_format($price, 2, '.', ','),
                            'change' => ($changePercent > 0 ? '+' : '') . number_format($changePercent, 2) . '%',
                            'isUp' => $changePercent >= 0,
                            'ytd' => '-' 
                        ];
                    }
                }
            }
            
            // Pad with mock data for missing assets to fill 5 slots
            $mockDefaults = [
                ['symbol' => 'USD/IDR', 'value' => '16,245', 'change' => '-0.21%', 'isUp' => false, 'ytd' => '-1.36%'],
                ['symbol' => '10Y IND YIELD', 'value' => '6.70%', 'change' => '-3.0 bps', 'isUp' => false, 'ytd' => '-28.0 bps'],
                ['symbol' => 'BRENT', 'value' => '$84.78', 'change' => '+0.92%', 'isUp' => true, 'ytd' => '-13.5%'],
                ['symbol' => 'GOLD', 'value' => '$2,355', 'change' => '+0.71%', 'isUp' => true, 'ytd' => '+12.6%'],
            ];
            
            foreach ($mockDefaults as $mock) {
                if (count($results) < 5) {
                    $results[] = $mock;
                }
            }
            
            return $results;
        });

        return Inertia::render('DeskBrief/Index', [
            'deskBrief' => $latestBrief,
            'realtimeSnapshots' => $snapshots
        ]);
    }
}
