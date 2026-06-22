<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$apiKey = env('SECTOR_API_KEY');
$start = now()->subDays(7)->format('Y-m-d');
$response = Illuminate\Support\Facades\Http::withHeaders([
    'Authorization' => $apiKey
])->get("https://api.sectors.app/v2/daily/BBCA.JK/?start={$start}");

echo substr($response->body(), 0, 500);
