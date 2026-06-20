<?php
$apiKey = "d8ra711r01qni6th66d0";
$urls = [
    "OANDA:USD_IDR",
    "FX:USDIDR",
    "BINANCE:BTCUSDT"
];

foreach ($urls as $sym) {
    $url = "https://finnhub.io/api/v1/quote?symbol=" . urlencode($sym) . "&token=d8ra711r01qni6th66cgd8ra711r01qni6th66d0";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    echo "$sym: $result\n";
}
