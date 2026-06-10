<?php
$html = file_get_contents('/home/hasan/Documents/hasanarofid/avenir/default-avenir/storage/app/website/hero.html');

$styles = '';
preg_match_all('/<style[^>]*>.*?<\/style>/is', $html, $styleMatches);
if (!empty($styleMatches[0])) {
    $styles = implode("\n", $styleMatches[0]);
}

$heroHtml = '';
$heroStart = strpos($html, '<div class="hero">');
if ($heroStart !== false) {
    $heroEnd1 = strpos($html, '<div class="guest-lock-content">', $heroStart);
    $heroEnd2 = strpos($html, '<div class="cnt">', $heroStart);
    $heroEnd3 = strpos($html, '<div class="art-body">', $heroStart);
    
    $ends = array_filter([$heroEnd1, $heroEnd2, $heroEnd3], function($pos) { return $pos !== false; });
    $heroEnd = !empty($ends) ? min($ends) : false;
    
    if ($heroEnd !== false) {
        $heroHtml = substr($html, $heroStart, $heroEnd - $heroStart);
    }
}

echo "Styles length: " . strlen($styles) . "\n";
echo "Hero length: " . strlen($heroHtml) . "\n";
echo "Hero snippet: " . substr($heroHtml, 0, 100) . "...\n";
