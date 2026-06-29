<?php
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

\App\Models\MarketStanceDaily::updateOrCreate(
    ['date' => $yesterday],
    ['score' => 38, 'label' => 'Selective Risk-On', 'breadth_score' => null, 'sector_score' => null]
);

\App\Models\MarketStanceDaily::updateOrCreate(
    ['date' => $today],
    ['score' => 42, 'label' => 'Selective Risk-On', 'breadth_score' => null, 'sector_score' => null]
);
