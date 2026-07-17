<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$snapshotId = DB::table('ownership_snapshots')->insertGetId([
    'period_date' => '2026-07-17',
    'file_path' => null,
    'created_at' => now(),
    'updated_at' => now(),
]);

$brokers = \App\Models\Broker::pluck('name', 'code')->toArray();
$brokersFile = storage_path('app/private/temp_brokers_manual.json');
file_put_contents($brokersFile, json_encode(['byCode' => $brokers]));

$cmd = escapeshellcmd("python3 scripts/build_ownership_excel.py") . " " . 
    escapeshellarg("/home/hasan/Documents/hasanarofid/avenir/default-avenir/prd-testing/ownership/new/peng-2026-07-08-00027-lima-persen.xlsx") . " " . 
    escapeshellarg("/home/hasan/Documents/hasanarofid/avenir/default-avenir/prd-testing/ownership/new/peng-06-00017-tipe (1).xlsx") . " " . 
    escapeshellarg("/home/hasan/Documents/hasanarofid/avenir/default-avenir/prd-testing/ownership/new/peng-06-00015-klasifikasi (1).xlsx") . " " . 
    escapeshellarg("/home/hasan/Documents/hasanarofid/avenir/default-avenir/prd-testing/ownership/new/peng-06-00015-satu-persen (1).xlsx") . " " . 
    escapeshellarg($brokersFile);

echo "Running: $cmd\n";
$output = shell_exec($cmd);

$jsonFileName = 'ownership_data_' . $snapshotId . '.json';
Storage::disk('public')->put('ownership_data/' . $jsonFileName, $output);

DB::table('ownership_snapshots')->where('id', $snapshotId)->update([
    'file_path' => 'ownership_data/' . $jsonFileName,
    'updated_at' => now(),
]);

echo "Done, snapshot ID: $snapshotId\n";
