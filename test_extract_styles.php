<?php
$html = file_get_contents('/home/hasan/Documents/hasanarofid/avenir/default-avenir/storage/app/website/hero.html');
$headStyles = '';
if (preg_match('/<head[\s>](.*?)<\/head>/is', $html, $headMatch)) {
    preg_match_all('/<style[^>]*>.*?<\/style>/is', $headMatch[1], $styleMatches);
    if (!empty($styleMatches[0])) {
        $headStyles = implode("\n", $styleMatches[0]);
    }
}
echo "Head styles length: " . strlen($headStyles) . "\n";
echo "First few chars: " . substr($headStyles, 0, 100) . "\n";
