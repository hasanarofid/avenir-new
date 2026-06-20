<?php
$key = "db7e3835eemsh2a8fb993c9736ebp1662b6jsn2867b91b073f";
$host = "yahoo-finance166.p.rapidapi.com";
$symbols = "BBCA.JK,BTC-USD,IDR=X";

$endpoints = [
    "https://$host/api/market/get-quote?region=US&symbols=$symbols",
    "https://$host/api/stock/get-price?region=US&symbol=BBCA.JK",
];

foreach ($endpoints as $url) {
    echo "Testing $url...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-rapidapi-host: $host",
        "x-rapidapi-key: $key"
    ]);
    $result = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    echo "HTTP " . $info['http_code'] . "\n";
    echo substr($result, 0, 300) . "\n\n";
}
