<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$results = \App\Models\MarketSnapshot::where('date', '2026-07-08')->whereIn('symbol_or_metric', ['OPEN', 'HIGH', 'LOW', 'VOLATILITY_PERCENTILE'])->get();
print_r($results->toArray());
