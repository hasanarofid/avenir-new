<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = \App\Models\MarketSnapshot::where('date', '2026-07-08')->where('symbol_or_metric', 'BREADTH_SCORE')->first();
echo 'Value: ' . ($s ? $s->value : 'not found') . ' Source: ' . ($s ? $s->source : '-');
