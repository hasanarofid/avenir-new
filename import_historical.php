<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\MarketSnapshot;
use Carbon\Carbon;

$file = '/home/hasanarofid/Downloads/Data Foreign Flow.xlsx';
$spreadsheet = IOFactory::load($file);
$worksheet = $spreadsheet->getActiveSheet();

function parseAmount($str) {
    if (!$str) return 0;
    $str = str_replace(',', '', strtoupper(trim($str)));
    $multiplier = 1;
    if (str_ends_with($str, 'T')) {
        $multiplier = 1000;
        $str = substr($str, 0, -1);
    } elseif (str_ends_with($str, 'B')) {
        $multiplier = 1;
        $str = substr($str, 0, -1);
    } elseif (str_ends_with($str, 'M')) {
        $multiplier = 0.001;
        $str = substr($str, 0, -1);
    }
    return floatval($str) * $multiplier;
}

$rows = $worksheet->toArray();
$count = 0;

$previousClose = null;

// The data is ordered from oldest to newest? No, it looks like it might be oldest to newest based on rows 3-7 being Jan 2025.
// Let's sort the array by date to safely calculate changes.
$dataRows = [];
foreach ($rows as $index => $row) {
    if ($index < 2) continue; // Skip headers
    if (empty($row[1])) continue; // Empty date
    
    $dateStr = $row[1];
    // if it's already a DateTime object from PhpSpreadsheet
    if ($dateStr instanceof \DateTime) {
        $date = Carbon::instance($dateStr);
    } else {
        // try to parse
        try {
            $date = Carbon::parse($dateStr);
        } catch (\Exception $e) {
            continue;
        }
    }
    
    $dataRows[] = [
        'date' => $date,
        'foreign' => $row[2],
        'market_value' => $row[3],
        'close' => $row[4],
        'open' => $row[5],
        'high' => $row[6],
        'low' => $row[7],
        'change_pct' => $row[9]
    ];
}

usort($dataRows, function($a, $b) {
    return $a['date']->timestamp <=> $b['date']->timestamp;
});

foreach ($dataRows as $i => $row) {
    $dateStr = $row['date']->toDateString();
    
    $foreign = parseAmount($row['foreign']);
    $marketValue = parseAmount($row['market_value']);
    $close = floatval(str_replace(',', '', $row['close']));
    $open = floatval(str_replace(',', '', $row['open']));
    $high = floatval(str_replace(',', '', $row['high']));
    $low = floatval(str_replace(',', '', $row['low']));
    
    $changeAbs = 0;
    $changePct = 0;
    if ($i > 0) {
        $prevClose = $dataRows[$i-1]['close'];
        $prevClose = floatval(str_replace(',', '', $prevClose));
        $changeAbs = $close - $prevClose;
        $changePct = $prevClose != 0 ? ($changeAbs / $prevClose) * 100 : 0;
    }

    // Insert FOREIGN_NET_TODAY
    MarketSnapshot::updateOrCreate(
        ['date' => $dateStr, 'symbol_or_metric' => 'FOREIGN_NET_TODAY'],
        ['value' => $foreign, 'source' => 'excel_import']
    );
    
    // Insert VALUE_TRADED_BN_IDR
    MarketSnapshot::updateOrCreate(
        ['date' => $dateStr, 'symbol_or_metric' => 'VALUE_TRADED_BN_IDR'],
        ['value' => $marketValue, 'source' => 'excel_import']
    );
    
    // Insert OPEN, HIGH, LOW
    MarketSnapshot::updateOrCreate(['date' => $dateStr, 'symbol_or_metric' => 'OPEN'], ['value' => $open, 'source' => 'excel_import']);
    MarketSnapshot::updateOrCreate(['date' => $dateStr, 'symbol_or_metric' => 'HIGH'], ['value' => $high, 'source' => 'excel_import']);
    MarketSnapshot::updateOrCreate(['date' => $dateStr, 'symbol_or_metric' => 'LOW'], ['value' => $low, 'source' => 'excel_import']);
    
    MarketSnapshot::updateOrCreate(
        ['date' => $dateStr, 'symbol_or_metric' => 'IHSG'],
        [
            'value' => $close, 
            'change_abs' => $changeAbs, 
            'change_pct' => $changePct,
            'source' => 'excel_import'
        ]
    );
    
    $count++;
}

// Create a CSV for ihsg_price_trend.py ONCE outside the loop
$csvPath = '/home/hasanarofid/Documents/hasanarofid/avenir/avenir-new/storage/app/generated_ihsg.csv';
$fp = fopen($csvPath, 'w');
fputcsv($fp, ['Date', 'Price', 'Open', 'High', 'Low', 'Vol.', 'Change %']);

foreach (array_reverse($dataRows) as $row) {
    $dateFormatted = $row['date']->format('m/d/Y');
    fputcsv($fp, [
        $dateFormatted,
        $row['close'],
        $row['open'],
        $row['high'],
        $row['low'],
        '0M',
        $row['change_pct']
    ]);
}
fclose($fp);

// Run ihsg_price_trend.py ONCE
$output = shell_exec('python3 scripts/python/ihsg_price_trend.py ' . escapeshellarg($csvPath));
$parsed = json_decode($output, true);

if ($parsed && isset($parsed['history'])) {
    foreach ($parsed['history'] as $item) {
        if (!isset($item['date'])) continue;
        $d = $item['date'];
        
        if (isset($item['prices_60d'])) {
            MarketSnapshot::updateOrCreate(
                ['date' => $d, 'symbol_or_metric' => 'IHSG'],
                [
                    'sparkline_json' => json_encode($item['prices_60d']),
                ]
            );
        }
        
        if (isset($item['volatility_percentile'])) {
            MarketSnapshot::updateOrCreate(
                ['date' => $d, 'symbol_or_metric' => 'VOLATILITY_PERCENTILE'],
                [
                    'value' => $item['volatility_percentile'],
                    'source' => 'python_calculated'
                ]
            );
        }
    }
}

echo "Imported $count rows and recalculated python metrics.\n";

