<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$engine = app(\App\Services\MarketIntelligence\ScoringEngine::class);
$gathered = $engine->gatherMarketData('2026-07-09');

$data = $gathered['marketData'];
$history = $data['flow_history'];

$targetDate = \Carbon\Carbon::parse($data['latestDate'] ?? '2026-07-09');
$count_5d = 0;
$foreign_net_5d = 0;
$total_market_value_5d = 0;
$positive_days_5d = 0;

foreach ($history as $h) {
    $hDate = \Carbon\Carbon::parse($h['date']);
    if ($targetDate->diffInDays($hDate) <= 7 && $count_5d < 5) {
        $foreign_net_5d += $h['foreign_net'];
        $total_market_value_5d += $h['market_value'];
        if ($h['foreign_net'] > 0) $positive_days_5d++;
        $count_5d++;
    }
}

$count_20d = 0;
$foreign_net_20d = 0;
$total_market_value_20d = 0;
foreach ($history as $h) {
    $hDate = \Carbon\Carbon::parse($h['date']);
    if ($targetDate->diffInDays($hDate) <= 30 && $count_20d < 20) {
        $foreign_net_20d += $h['foreign_net'];
        $total_market_value_20d += $h['market_value'];
        $count_20d++;
    }
}
$avg_market_value_20d = $count_20d > 0 ? $total_market_value_20d / $count_20d : 0;

$foreign_net_1d = $history[0]['foreign_net'] ?? 0;
$market_value_1d = $history[0]['market_value'] ?? 0;

$foreign_intensity_1d = $market_value_1d > 0 ? $foreign_net_1d / $market_value_1d : 0;
$foreign_intensity_5d = $total_market_value_5d > 0 ? $foreign_net_5d / $total_market_value_5d : 0;
$foreign_intensity_20d = $total_market_value_20d > 0 ? $foreign_net_20d / $total_market_value_20d : 0;

$normal_5d_value = $avg_market_value_20d * 5;
$liquidity_ratio = $normal_5d_value > 0 ? $total_market_value_5d / $normal_5d_value : 0;

echo "1D net: $foreign_net_1d, value: $market_value_1d, int: $foreign_intensity_1d\n";
echo "5D net: $foreign_net_5d, value: $total_market_value_5d, int: $foreign_intensity_5d\n";
echo "20D net: $foreign_net_20d, value: $total_market_value_20d, int: $foreign_intensity_20d\n";
echo "Avg 20D val: $avg_market_value_20d\n";
echo "Liquidity ratio: $liquidity_ratio\n";
echo "Positive 5d: $positive_days_5d\n";

$score = $engine->calculateFlowScore($data);
echo "Final flow score: $score\n";
