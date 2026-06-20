<?php
$apiKey = "d8ra711r01qni6th66d0"; // Wait, the key is d8ra711r01qni6th66cgd8ra711r01qni6th66d0
$url = "https://finnhub.io/api/v1/quote?symbol=UNTR.JK&token=d8ra711r01qni6th66cgd8ra711r01qni6th66d0";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($ch);
curl_close($ch);
echo "UNTR.JK: " . $result . "\n";

$url2 = "https://finnhub.io/api/v1/quote?symbol=OANDA:USD_IDR&token=d8ra711r01qni6th66cgd8ra711r01qni6th66d0";
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $url2);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
$result2 = curl_exec($ch2);
curl_close($ch2);
echo "USD_IDR: " . $result2 . "\n";

$url3 = "https://finnhub.io/api/v1/quote?symbol=BINANCE:BTCUSDT&token=d8ra711r01qni6th66cgd8ra711r01qni6th66d0";
$ch3 = curl_init();
curl_setopt($ch3, CURLOPT_URL, $url3);
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
$result3 = curl_exec($ch3);
curl_close($ch3);
echo "BTCUSDT: " . $result3 . "\n";

