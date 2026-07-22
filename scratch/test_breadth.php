<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$engine = new \App\Services\MarketIntelligence\ScoringEngine();

$dates = ['2026-07-08'];

foreach ($dates as $date) {
    $gathered = $engine->gatherMarketData($date);
    $md = $gathered['marketData'];
    print_r([
        'date' => $date,
        'advancers' => $md['advancers'],
        'decliners' => $md['decliners'],
        'stable' => $md['stable'],
        'snapshot' => $md['breadth_score_snapshot'],
    ]);
}
