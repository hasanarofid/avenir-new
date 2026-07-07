<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$fullPath = '/home/hasanarofid/Downloads/ds_260707.pdf';
$scriptPath = base_path('prd-testing/scripts/python/parse_idx_pdf.py');
$command = escapeshellcmd("python3 {$scriptPath} {$fullPath}");
$output = shell_exec($command);
$parsed = json_decode($output, true);

if (!$parsed || isset($parsed['error'])) {
    echo "Gagal memproses PDF: " . ($parsed['error'] ?? 'Unknown error');
    exit;
}

$date = \Carbon\Carbon::parse($parsed['date'])->toDateString();

// Insert to MarketSnapshots
$metrics = [
    'IHSG' => [
        'value' => $parsed['ihsg_close'] ?? 0,
        'change_abs' => $parsed['ihsg_change_abs'] ?? 0,
        'change_pct' => $parsed['ihsg_change_pct'] ?? 0
    ],
    'VALUE_TRADED_BN_IDR' => [
        'value' => $parsed['value_traded_bn_idr'] ?? 0
    ],
    'FOREIGN_NET_TODAY' => [
        'value' => $parsed['foreign_net_today'] ?? 0
    ],
    'ADVANCERS' => [
        'value' => $parsed['advancers'] ?? 0
    ],
    'DECLINERS' => [
        'value' => $parsed['decliners'] ?? 0
    ],
    'STABLE' => [
        'value' => $parsed['stable'] ?? 0
    ]
];

foreach ($metrics as $metric => $data) {
    \App\Models\MarketSnapshot::updateOrCreate(
        ['date' => $date, 'symbol_or_metric' => $metric],
        array_merge($data, ['source' => 'pdf_upload'])
    );
}

// Insert to SectorBiasDaily
if (isset($parsed['sectors']) && is_array($parsed['sectors'])) {
    foreach ($parsed['sectors'] as $sectorName => $return1d) {
        \App\Models\SectorBiasDaily::updateOrCreate(
            ['date' => $date, 'sector' => $sectorName],
            ['return_1d' => $return1d, 'bias' => 'neutral']
        );
    }
}

// Generate Draft automatically
\Illuminate\Support\Facades\Artisan::call('deskbrief:draft', [
    'date' => $date,
    '--force' => true
]);

echo "Success! Output:\n" . \Illuminate\Support\Facades\Artisan::output();
