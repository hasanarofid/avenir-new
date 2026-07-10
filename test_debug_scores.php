<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$date = '2026-07-08';
$bridge = new \App\Services\MarketIntelligence\PythonBridge();

// Export CSVs WITHOUT cleanup
$ohlcCsv = $bridge->exportOhlcCsv($date, 300);
$flowCsv = $bridge->exportFlowCsv($date, 60);

// Show last 5 rows of each
echo "=== OHLC CSV (last 5 rows) ===\n";
$lines = file($ohlcCsv);
echo implode('', array_slice($lines, 0, 1)); // header
echo implode('', array_slice($lines, -5));   // last 5

echo "\n=== FLOW CSV (last 10 rows) ===\n";
$flines = file($flowCsv);
echo implode('', array_slice($flines, 0, 1)); // header
echo implode('', array_slice($flines, -10));  // last 10

// Run scripts verbosely
$tempDir = $bridge->getTempDir();

echo "\n=== FLOW SCORE JSON ===\n";
$flOutDir = $tempDir . "/fl_debug";
@mkdir($flOutDir, 0755, true);
$flJson = $flOutDir . '/latest_flow_score.json';
shell_exec("cd " . escapeshellarg(base_path('scripts/python/avenir_regime_engine_py')) . " && python3 flow.py --input " . escapeshellarg($flowCsv) . " --output-dir " . escapeshellarg($flOutDir) . " 2>&1");
if (file_exists($flJson)) {
    $d = json_decode(file_get_contents($flJson), true);
    foreach (['date','flow_score','flow_label','foreign_net_1d','foreign_net_5d','foreign_net_20d','market_value_1d','total_market_value_5d','total_market_value_20d','avg_market_value_20d','foreign_intensity_1d','foreign_intensity_5d','foreign_intensity_20d','positive_foreign_flow_days_5d','foreign_flow_trend_score','flow_consistency_score','liquidity_confirmation_score'] as $k) {
        if (isset($d[$k])) {
            echo "  $k: " . $d[$k] . "\n";
        }
    }
}

echo "\n=== VOLATILITY JSON ===\n";
$vsOutDir = $tempDir . "/vs_debug";
@mkdir($vsOutDir, 0755, true);
$vsJson = $vsOutDir . '/latest_volatility_stability_score.json';
shell_exec("cd " . escapeshellarg(base_path('scripts/python/avenir_regime_engine_py')) . " && python3 volatility.py --input " . escapeshellarg($ohlcCsv) . " --output-dir " . escapeshellarg($vsOutDir) . " 2>&1");
if (file_exists($vsJson)) {
    $d = json_decode(file_get_contents($vsJson), true);
    foreach (['date','volatility_stability_score','volatility_stability_label','volatility_percentile','volatility_regime_score','liquidity_ratio','liquidity_quality_score','intraday_range_pct','intraday_range_score','ihsg_return_1d','return_shock_score','close_location','close_location_score','avg_market_value_20d'] as $k) {
        if (isset($d[$k])) {
            echo "  $k: " . $d[$k] . "\n";
        }
    }
} else {
    $out = shell_exec("cd " . escapeshellarg(base_path('scripts/python/avenir_regime_engine_py')) . " && python3 volatility.py --input " . escapeshellarg($ohlcCsv) . " --output-dir " . escapeshellarg($vsOutDir) . " 2>&1");
    echo "ERROR: $out\n";
}
