<?php
$html = file_get_contents('/home/hasan/Documents/hasanarofid/avenir/default-avenir/storage/app/website/hero.html');

$heroStart = strpos($html, '<div class="hero">');
echo "Hero start: " . $heroStart . "\n";
$heroEnd1 = strpos($html, '<div class="guest-lock-content">', $heroStart);
$heroEnd2 = strpos($html, '<div class="cnt">', $heroStart);
$heroEnd3 = strpos($html, '<div class="art-body">', $heroStart);

echo "End1: " . var_export($heroEnd1, true) . "\n";
echo "End2: " . var_export($heroEnd2, true) . "\n";
echo "End3: " . var_export($heroEnd3, true) . "\n";
