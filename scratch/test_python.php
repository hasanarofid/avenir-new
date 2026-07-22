<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$date = '2026-07-20';
$bridge = new \App\Services\MarketIntelligence\PythonBridge();
$tempDir = $bridge->getTempDir();

$ringkasan = '/home/hasanarofid/Documents/hasanarofid/avenir/excel_terbaru/storage/app/private/ringkasan_saham/xDtPemlaNovkerR1WR1eTqxE8jcBkvNwQthUtDkb.xlsx';
$index = '/home/hasanarofid/Documents/hasanarofid/avenir/excel_terbaru/storage/app/private/index_summary/zo2oGS1txt9WLX27oxQc5ZUJ52TyhOQAfTeDNJHd.xlsx';
$master = storage_path('app/sector_master.csv');

$srOutDir = $tempDir . '/sr_debug';
@mkdir($srOutDir, 0755, true);
echo shell_exec('cd scripts/python/avenir_regime_engine_py && python3 sector_rotation.py --stocks ' . escapeshellarg($ringkasan) . ' --sector-master ' . escapeshellarg($master) . ' --output-dir ' . escapeshellarg($srOutDir) . ' 2>&1');

$mbOutDir = $tempDir . '/mb_debug';
@mkdir($mbOutDir, 0755, true);
echo shell_exec('cd scripts/python/avenir_regime_engine_py && python3 market_breadth.py --input ' . escapeshellarg($ringkasan) . ' --output-dir ' . escapeshellarg($mbOutDir) . ' 2>&1');

$ohlcCsv = $bridge->exportOhlcCsv($date, 300);
$vsOutDir = $tempDir . '/vs_debug';
@mkdir($vsOutDir, 0755, true);
echo shell_exec('cd scripts/python/avenir_regime_engine_py && python3 volatility.py --input ' . escapeshellarg($ohlcCsv) . ' --output-dir ' . escapeshellarg($vsOutDir) . ' 2>&1');

