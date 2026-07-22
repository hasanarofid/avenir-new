<?php
$output = shell_exec('python3 scripts/python/ihsg_price_trend.py "/home/hasanarofid/Documents/hasanarofid/avenir/ready-upload/Jakarta Stock Exchange Composite Index Historical Data(1).csv"');
$parsed = json_decode($output, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Decode Error: " . json_last_error_msg() . "\n";
} else {
    $history = $parsed['history'] ?? (isset($parsed['date']) ? [$parsed] : []);
    $first = $history[0];
    echo "First row date: " . $first['date'] . "\n";
    echo "Open: " . $first['open'] . "\n";
    echo "High: " . $first['high'] . "\n";
    echo "Low: " . $first['low'] . "\n";
    echo "Close: " . $first['close'] . "\n";
    echo "All string values in all rows:\n";
    $has_comma = false;
    foreach($history as $idx => $row) {
        foreach($row as $k => $v) {
            if(is_string($v) && strpos($v, ',') !== false) {
                echo "WARNING: Row $idx Key $k has a comma: $v\n";
                $has_comma = true;
            }
        }
    }
    if (!$has_comma) echo "No commas found in any field.\n";
    echo "Done.\n";
}
