<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$engine = app(\App\Services\MarketIntelligence\KeyDriversEngine::class);
$drivers = $engine->buildIhsgKeyDrivers('LQ45', 5);
echo json_encode($drivers, JSON_PRETTY_PRINT);
