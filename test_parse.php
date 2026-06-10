<?php
require __DIR__.'/vendor/autoload.php';

$html = file_get_contents(__DIR__.'/app/website/baru/katalog.html');
$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
$xpath = new DOMXPath($dom);

$cards = $xpath->query('//div[contains(@class, "card")]');
$results = [];

foreach ($cards as $card) {
    // skip if it doesn't have data-date
    if (!$card->hasAttribute('data-date')) continue;

    $date = $card->getAttribute('data-date');
    $tags = $card->getAttribute('data-tags');

    $titleNode = $xpath->query('.//h2', $card)->item(0);
    $title = $titleNode ? $titleNode->nodeValue : '';

    // inner HTML of subtitle
    $subtitleNode = $xpath->query('.//p[contains(@class, "card-sub")]', $card)->item(0);
    $subtitle = '';
    if ($subtitleNode) {
        $subtitleHtml = '';
        foreach ($subtitleNode->childNodes as $child) {
            $subtitleHtml .= $dom->saveHTML($child);
        }
        $subtitle = trim($subtitleHtml);
    }

    $tickerNode = $xpath->query('.//span[contains(@class, "ticker")]', $card)->item(0);
    $ticker = $tickerNode ? trim(str_replace('IDX:', '', $tickerNode->nodeValue)) : null;

    $sectorNode = $xpath->query('.//span[contains(@class, "sector")]', $card)->item(0);
    $sectorHtml = '';
    if ($sectorNode) {
        foreach ($sectorNode->childNodes as $child) {
            $sectorHtml .= $dom->saveHTML($child);
        }
    }
    $sector = trim($sectorHtml);

    $cmvNodes = $xpath->query('.//div[contains(@class, "card-m")]//div[contains(@class, "cmv")]', $card);
    $revenue = $cmvNodes->item(0) ? trim($cmvNodes->item(0)->nodeValue) : null;
    $patmi = $cmvNodes->item(1) ? trim($cmvNodes->item(1)->nodeValue) : null;
    $sales = $cmvNodes->item(2) ? trim($cmvNodes->item(2)->nodeValue) : null;

    $btnNode = $xpath->query('.//button[contains(@class, "cta-unlock")]', $card)->item(0);
    $price = $btnNode ? $btnNode->getAttribute('data-price') : null;
    $slug = null;
    if ($btnNode) {
        $onclick = $btnNode->getAttribute('onclick');
        if (preg_match("/'([^']+)\.html'/", $onclick, $matches)) {
            $slug = $matches[1];
        }
    }

    $results[] = [
        'title' => $title,
        'subtitle' => $subtitle,
        'ticker' => $ticker,
        'sector' => $sector,
        'revenue' => $revenue,
        'patmi' => $patmi,
        'sales' => $sales,
        'price' => $price,
        'tags' => $tags,
        'date' => $date,
        'slug' => $slug
    ];
}

echo json_encode($results, JSON_PRETTY_PRINT);
