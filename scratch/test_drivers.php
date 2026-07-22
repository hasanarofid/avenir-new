<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$engine = app(\App\Services\MarketIntelligence\KeyDriversEngine::class);
$manualInputs = [
    'RUPIAH_BI_SBN_YIELD' => [
        'usd_idr_change_5d' => null,
        'sbn_10y' => null,
        'sbn_10y_change_5d' => null,
        'bi_stance' => 'neutral',
    ]
];
$data = $engine->buildIhsgKeyDrivers('LQ45', 5, '2026-07-09', $manualInputs);
foreach ($data as $d) {
    echo $d['rank'] . " " . $d['title'] . "\n";
}
