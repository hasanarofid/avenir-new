<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$engine = new \App\Services\MarketIntelligence\ScoringEngine();

$dates = ['2026-07-01','2026-07-02','2026-07-03','2026-07-06','2026-07-07','2026-07-08','2026-07-09'];
foreach ($dates as $date) {
    $gathered = $engine->gatherMarketData($date);
    $md = $gathered['marketData'];
    $result = $engine->calculateMarketRegime($md);
    echo "$date | PT:{$result['component_scores']['price_trend']} BT:{$result['component_scores']['breadth']} FL:{$result['component_scores']['flow']} SR:{$result['component_scores']['sector_rotation']} VL:{$result['component_scores']['volatility_liquidity']} | Final:{$result['final_score']} | {$result['regime']}\n";
    echo "  IHSG={$md['close']} MA20={$md['ma20']} R5d={$md['ret_5d']} | ADV={$md['advancers']} DEC={$md['decliners']} STB={$md['stable']} | FlowNet5d={$md['foreign_net_5d']} | ValPct={$md['volatility_percentile']}\n";
}
