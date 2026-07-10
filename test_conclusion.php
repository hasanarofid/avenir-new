<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$brief = \App\Models\DeskBrief::where('date', '2026-07-09')->first();
echo "CONCLUSION:\n";
echo $brief->market_read . "\n";
