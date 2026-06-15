<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$researches = App\Models\Research::all();
$updated = 0;

foreach ($researches as $research) {
    echo "Fetching: {$research->slug}... ";
    
    // Skip HERO since we did it manually
    if ($research->slug === 'hero') {
        echo "Skipped (Already done)\n";
        continue;
    }

    $url = "https://demo.researchavenir.com/katalog/{$research->slug}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$html) {
        echo "Failed (HTTP $httpCode)\n";
        continue;
    }
    
    if (preg_match('/data-page="([^"]+)"/', $html, $matches)) {
        $dataPageStr = str_replace('&quot;', '"', $matches[1]);
        $dataPage = json_decode($dataPageStr, true);
        
        if (isset($dataPage['props']['research']['content'])) {
            $content = $dataPage['props']['research']['content'];
            $decoded = html_entity_decode($content, ENT_QUOTES);
            
            $research->content = $decoded;
            $research->save();
            $updated++;
            echo "Success\n";
        } else {
            echo "Content not found in JSON\n";
        }
    } else {
        echo "data-page not found\n";
    }
}

echo "\nCompleted. Updated $updated researches.\n";
