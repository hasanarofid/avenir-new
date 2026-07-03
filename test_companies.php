<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$api = app(\App\Services\SectorsApiService::class);
$data = $api->fetchCompaniesUniverse('LQ45', 1);
echo json_encode($data, JSON_PRETTY_PRINT);
