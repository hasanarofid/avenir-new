<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MarketStanceDaily;
use App\Services\MarketIntelligence\ScoringEngine;

$data = [
    ['date' => '2026-07-01', 'pt' => 32, 'mb' => 65, 'flow' => 15, 'sr' => 74, 'st' => 53, 'final' => 45],
    ['date' => '2026-07-02', 'pt' => 34, 'mb' => 66, 'flow' => 17, 'sr' => 92, 'st' => 54, 'final' => 49],
    ['date' => '2026-07-03', 'pt' => 47, 'mb' => 80, 'flow' => 24, 'sr' => 94, 'st' => 62, 'final' => 59],
    ['date' => '2026-07-06', 'pt' => 53, 'mb' => 66, 'flow' => 24, 'sr' => 87, 'st' => 60, 'final' => 56],
    ['date' => '2026-07-07', 'pt' => 66, 'mb' => 69, 'flow' => 20, 'sr' => 89, 'st' => 63, 'final' => 61],
    ['date' => '2026-07-08', 'pt' => 48, 'mb' => 28, 'flow' => 19, 'sr' => 16, 'st' => 43, 'final' => 32],
    ['date' => '2026-07-09', 'pt' => 48, 'mb' => 60, 'flow' => 21, 'sr' => 77, 'st' => 74, 'final' => 53],
];

$engine = new ScoringEngine();

foreach ($data as $row) {
    // Generate label based on final score
    $label = $engine->classifyMarketRegime([], $row['final']);

    MarketStanceDaily::updateOrCreate(
        ['date' => $row['date']],
        [
            'score'          => $row['final'],
            'label'          => $label,
            'foreign_score'  => $row['flow'],
            'breadth_score'  => $row['mb'],
            'momentum_score' => $row['pt'],
            'rupiah_score'   => $row['st'],
            'yield_score'    => 0,
            'sector_score'   => $row['sr'],
        ]
    );
    echo "Inserted for {$row['date']}\n";
}
