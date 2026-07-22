<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$engine = new \App\Services\MarketIntelligence\ScoringEngine();
$gathered = $engine->gatherMarketData('2026-07-03');
$md = $gathered['marketData'];

echo "positive_sectors: " . $md['positive_sectors'] . "\n";
echo "total_sectors: " . $md['total_sectors'] . "\n";
echo "sector_positive_ratio: " . $md['sector_positive_ratio'] . "\n";
echo "pct_above_ma20: " . $md['pct_above_ma20'] . "\n";
echo "leadership_concentration: " . $md['leadership_concentration'] . "\n";
echo "leadership_consistency_days: " . $md['leadership_consistency_days'] . "\n";
echo "advancers: " . $md['advancers'] . "\n";
echo "decliners: " . $md['decliners'] . "\n";
echo "stable: " . $md['stable'] . "\n";
echo "breadth_score_snapshot: " . $md['breadth_score_snapshot'] . "\n";

// Simulate breadth
$adv = $md['advancers'];
$dec = $md['decliners'];
$stb = $md['stable'];
echo "\nADV/(ADV+DEC): " . ($adv/($adv+$dec)*100) . "\n";
echo "ADV/(ADV+DEC+STB): " . ($adv/($adv+$dec+$stb)*100) . "\n";

// Simulate sector score
$pos = $md['positive_sectors'];
$tot = $md['total_sectors'];
echo "\nSector score: " . ($tot > 0 ? ($pos/$tot)*100 : 0) . "\n";
