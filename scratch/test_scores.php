<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$engine = new \App\Services\MarketIntelligence\ScoringEngine();
$data = $engine->gatherMarketData('2026-07-08')['marketData'];
print_r($engine->calculateMarketRegime($data));
