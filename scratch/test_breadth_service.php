<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$date       = '2026-07-08';
$excelPath  = '/home/hasanarofid/Documents/hasanarofid/avenir/ready-upload/Ringkasan Saham-20260708.xlsx';
$masterPath = '/home/hasanarofid/Documents/hasanarofid/avenir/ready-upload/Financial Data and Ratio - May 2026 (1).xlsx';

echo "=== Testing BreadthService for date: $date ===\n\n";

$service = new \App\Services\MarketIntelligence\BreadthService(
    new \App\Services\MarketIntelligence\PythonBridge()
);

$results = $service->calculateAndStore($date, $excelPath, $masterPath);

echo "Market Breadth:\n";
$mb = $results['market_breadth'] ?? [];
$keys = ['market_breadth_score','market_breadth_label','ad_score','strong_movers_score','mcap_breadth_score','value_breadth_score','active_participation_score','advancers','decliners','stable'];
foreach ($keys as $k) {
    if (isset($mb[$k])) echo "  $k: {$mb[$k]}\n";
}

echo "\nSector Rotation:\n";
$sr = $results['sector_rotation'] ?? [];
$srKeys = ['sector_rotation_score','sector_rotation_label','positive_sector_count','total_sector_count','leader_sector','laggard_sector'];
foreach ($srKeys as $k) {
    if (isset($sr[$k])) echo "  $k: {$sr[$k]}\n";
}
