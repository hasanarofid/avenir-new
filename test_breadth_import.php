<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$file = '/home/hasanarofid/Documents/hasanarofid/avenir/ready-upload/Ringkasan Saham-20260708.xlsx';
$import = new \App\Imports\MarketBreadthImport();
\Maatwebsite\Excel\Facades\Excel::import($import, $file);

print_r($import->results);
