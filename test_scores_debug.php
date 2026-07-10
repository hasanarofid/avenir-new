<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$engine = new class extends \App\Services\MarketIntelligence\ScoringEngine {
    public function calculateVolatilityLiquidityScore(array $data): int {
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
        return parent::calculateVolatilityLiquidityScore($data);
    }
};

$gathered = $engine->gatherMarketData('2026-07-08');
echo "Vol score: " . $engine->calculateVolatilityLiquidityScore($gathered['marketData']) . "\n";
