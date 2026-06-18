<?php
$json = file_get_contents("https://query2.finance.yahoo.com/v8/finance/chart/%5EJKSE");
$data = json_decode($json, true);
$result = $data['chart']['result'][0] ?? null;
if ($result) {
    $timestamps = $result['timestamp'] ?? [];
    $closes = $result['indicators']['quote'][0]['close'] ?? [];
    $filtered = [];
    foreach ($closes as $i => $close) {
        if ($close !== null) {
            $filtered[] = round($close, 2);
        }
    }
    // Subsample to max 50 points
    $step = max(1, floor(count($filtered) / 50));
    $chartData = [];
    for ($i = 0; $i < count($filtered); $i += $step) {
        $chartData[] = $filtered[$i];
    }
    echo "Total valid points: " . count($filtered) . "\n";
    echo "Subsampled points: " . count($chartData) . "\n";
    echo "First 5: " . implode(", ", array_slice($chartData, 0, 5)) . "\n";
} else {
    echo "No result";
}
