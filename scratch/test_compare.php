<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bridge = new \App\Services\MarketIntelligence\PythonBridge();
$date = '2026-07-07';

$ohlcCsv = $bridge->exportOhlcCsv($date, 300);
$tempDir = $bridge->getTempDir();

$ptOutDir = $tempDir . "/pt_debug";
@mkdir($ptOutDir, 0755, true);
shell_exec("cd scripts/python/avenir_regime_engine_py && python3 price_trend.py --input " . escapeshellarg($ohlcCsv) . " --output-dir " . escapeshellarg($ptOutDir));
$ptJson = json_decode(file_get_contents($ptOutDir . '/latest_price_trend_score.json'), true);

echo "Date: $date\n";
echo "PT: " . $ptJson['price_trend_score'] . "\n";
