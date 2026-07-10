<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$date = '2026-07-08';

echo "=== Testing PythonBridge for date: $date ===\n\n";

$engine = new \App\Services\MarketIntelligence\ScoringEngine();
$scores = $engine->runPythonScoring($date);

echo "Component Scores:\n";
foreach ($scores as $k => $v) {
    echo "  $k: $v\n";
}

$final = $engine->calculateFinalRegimeScore($scores);
$label = $engine->classifyMarketRegime($scores, $final);

echo "\nFinal Score: $final\n";
echo "Label: $label\n";
