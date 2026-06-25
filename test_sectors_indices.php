<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$apiKey = env('SECTOR_API_KEY');

$urls = [
    'LQ45' => "https://api.sectors.app/v2/index-daily/lq45/",
    'IDX30' => "https://api.sectors.app/v2/index-daily/idx30/",
    'IDXBASIC' => "https://api.sectors.app/v2/index-daily/idxbasic/",
    'SECTORS_PERF' => "https://api.sectors.app/v2/sectors/",
];

foreach ($urls as $name => $url) {
    echo "Testing $name...\n";
    $response = \Illuminate\Support\Facades\Http::withHeaders(['Authorization' => $apiKey])->get($url);
    if ($response->successful()) {
        echo substr(json_encode($response->json()), 0, 200) . "\n";
    } else {
        echo "Failed: " . $response->status() . "\n";
        echo substr($response->body(), 0, 200) . "\n";
    }
}
