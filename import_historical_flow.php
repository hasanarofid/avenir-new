<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$csvFile = '/tmp/flow_clean.csv';
if (!file_exists($csvFile)) {
    echo "CSV not found at $csvFile\n";
    exit(1);
}

$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle);
// date,foreign_net,market_value,close,open,high,low,change_pct

$importedCount = 0;
while (($row = fgetcsv($handle)) !== false) {
    $data = array_combine($header, $row);
    
    $date = $data['date']; // 2025-01-02
    
    $metrics = [
        'FOREIGN_NET_TODAY' => $data['foreign_net'],
        'VALUE_TRADED_BN_IDR' => $data['market_value'],
        'IHSG' => $data['close'],
        'OPEN' => $data['open'],
        'HIGH' => $data['high'],
        'LOW' => $data['low'],
    ];
    
    foreach ($metrics as $metric => $val) {
        if ($val === '' || $val === null || $val === 'NaN') continue;
        
        \App\Models\MarketSnapshot::updateOrCreate(
            ['date' => $date, 'symbol_or_metric' => $metric],
            ['value' => $val, 'source' => 'excel_import_historical']
        );
    }
    $importedCount++;
}
fclose($handle);

echo "Imported $importedCount days of flow & IHSG data into market_snapshots.\n";
