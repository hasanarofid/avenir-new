<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$engine = new \App\Services\MarketIntelligence\ScoringEngine();

$data = [
    'value_traded' => 10541,
    'avg_value_20d' => 10000, // Guessing average value traded
    'high' => 5984.47,
    'low' => 5872.02,
    'close' => 5873.37,
    'open' => 5984.18,
    'ihsg_return_1d' => -1.89,
    'daily_range_pct' => 0.019, // (5984.47 - 5872.02)/5872.02
    'avg_range_20d' => 0.015,
    'volatility_percentile' => 0.6166666666666667
];
echo "Volatility Score: " . $engine->calculateVolatilityLiquidityScore($data) . "\n";
