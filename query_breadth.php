<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$snapshots = \App\Models\MarketSnapshot::where('date', '2026-07-09')->get();
foreach ($snapshots as $s) {
    echo "{$s->symbol_or_metric}: {$s->value}\n";
}
