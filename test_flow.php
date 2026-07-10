<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$engine = new \App\Services\MarketIntelligence\ScoringEngine();

$dates = ['2026-07-01', '2026-07-02', '2026-07-03', '2026-07-06', '2026-07-07', '2026-07-08', '2026-07-09'];

echo str_pad("Date", 12) . " | " . 
     str_pad("Flow", 6) . " | " . 
     str_pad("Breadth", 8) . " | " . 
     str_pad("Stab", 6) . " | " . 
     str_pad("Final", 6) . "\n";
echo str_repeat("-", 50) . "\n";

foreach ($dates as $date) {
    $gathered = $engine->gatherMarketData($date);
    $md = $gathered['marketData'];
    
    $flow = $engine->calculateFlowScore($md);
    $breadth = $engine->calculateBreadthScore($md);
    $stab = $engine->calculateVolatilityLiquidityScore($md);
    
    // Simulate final roughly (equal weights for testing)
    $final = round(($flow + $breadth + $stab) / 3);
    
    echo str_pad($date, 12) . " | " . 
         str_pad($flow, 6) . " | " . 
         str_pad($breadth, 8) . " | " . 
         str_pad($stab, 6) . " | " . 
         str_pad($final, 6) . "\n";
}
