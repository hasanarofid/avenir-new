<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$engine = new \App\Services\MarketIntelligence\ScoringEngine();

$dates = ['2026-07-07', '2026-07-08', '2026-07-09'];
foreach ($dates as $date) {
    echo "\n==== $date ====\n";
    $gathered = $engine->gatherMarketData($date);
    $marketData = $gathered['marketData'];
    $result = $engine->calculateMarketRegime($marketData);
    print_r($result['component_scores']);
    echo "Final: " . $result['final_score'] . "\n";
}
