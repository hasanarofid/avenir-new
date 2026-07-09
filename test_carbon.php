<?php
require 'vendor/autoload.php';
$targetDate = \Carbon\Carbon::parse('2026-07-09');
$hDate = \Carbon\Carbon::parse('2026-06-17');
echo "Diff: " . $targetDate->diffInDays($hDate) . "\n";
