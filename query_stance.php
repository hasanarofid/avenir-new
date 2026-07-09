<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (['2026-07-08', '2026-07-09'] as $date) {
    $s = \App\Models\MarketStanceDaily::where('date', $date)->first();
    echo "$date: rupiah_score=" . ($s->rupiah_score ?? 'null') . "\n";
}
