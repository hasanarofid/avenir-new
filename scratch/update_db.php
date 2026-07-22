<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\MarketSnapshot::updateOrCreate(
    ['date' => '2026-07-09', 'symbol_or_metric' => 'BREADTH_SCORE'],
    ['value' => 57, 'source' => 'pdf_upload']
);

$s = \App\Models\MarketStanceDaily::firstOrNew(['date' => '2026-07-09']);
$s->breadth_score = 57;
$s->save();

