<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$brief = App\Models\DeskBrief::find(1);
echo json_encode($brief->drivers, JSON_PRETTY_PRINT);
