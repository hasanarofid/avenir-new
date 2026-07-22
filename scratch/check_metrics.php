<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$metrics = \App\Models\MarketSnapshot::select('symbol_or_metric')->distinct()->pluck('symbol_or_metric');
echo "Metrics: \n" . implode("\n", $metrics->toArray());
