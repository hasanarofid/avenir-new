<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = \App\Models\MarketSnapshot::whereIn('symbol_or_metric', ['OPEN', 'HIGH', 'LOW'])->count();
echo "OHLC records: $count\n";
