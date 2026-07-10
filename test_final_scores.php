<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$rows = \App\Models\MarketStanceDaily::where('date','>=','2026-07-01')->orderBy('date','desc')->get();
foreach($rows as $r) {
    echo $r->date . " | Score:" . $r->score . " | " . $r->label . " | PT:" . $r->momentum_score . " MB:" . $r->breadth_score . " FL:" . $r->foreign_score . " SR:" . $r->sector_score . " VS:" . $r->rupiah_score . "\n";
}
