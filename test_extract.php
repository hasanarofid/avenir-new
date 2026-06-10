<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$slug = 'hero';
$filePath = storage_path("app/website/{$slug}.html");
$content = null;

if (file_exists($filePath)) {
    $html = file_get_contents($filePath);

    // 1. Ambil konten di dalam guest-lock-content (untuk riset berbayar guest)
    $startToken = '<div class="guest-lock-content">';
    $endToken   = '<div class="guest-lock-overlay"';
    $startPos   = strpos($html, $startToken);
    if ($startPos !== false) {
        $startPos += strlen($startToken);
        $endPos = strpos($html, $endToken, $startPos);
        if ($endPos !== false) {
            $content = trim(substr($html, $startPos, $endPos - $startPos));
            echo "Matched 1\n";
        }
    }

    // 2. Fallback: ambil konten di dalam .cnt (misal CRM.html)
    if (!$content) {
        $startToken = '<div class="cnt">';
        $endToken   = '<footer';
        $startPos   = strpos($html, $startToken);
        if ($startPos !== false) {
            $endPos = strpos($html, $endToken, $startPos);
            if ($endPos !== false) {
                $content = trim(substr($html, $startPos, $endPos - $startPos));
                echo "Matched 2\n";
            } else {
                $content = trim(substr($html, $startPos));
                echo "Matched 2 (no footer)\n";
            }
        }
    }

    // 3. Fallback: ambil konten di dalam .art-body
    if (!$content) {
        $startToken = '<div class="art-body">';
        $startPos   = strpos($html, $startToken);
        if ($startPos !== false) {
            $content = trim(substr($html, $startPos));
            // Hapus footer jika ikut terbawa
            if (($fPos = strpos($content, '<footer')) !== false) {
                $content = substr($content, 0, $fPos);
            }
            echo "Matched 3\n";
        }
    }

    // 4. Fallback: ambil dari <body> tapi hilangkan nav, drawer, dan hero
    if (!$content) {
        $startPos = strpos($html, '<body>');
        $endPos   = strpos($html, '</body>');
        if ($startPos !== false && $endPos !== false) {
            $rawBody = substr($html, $startPos + strlen('<body>'), $endPos - $startPos - strlen('<body>'));
            // Buang elemen header legacy yang merusak tampilan
            $rawBody = preg_replace('/<div class="nav">.*?<\/div>\s*<\/div>/s', '', $rawBody);
            $rawBody = preg_replace('/<div class="drawer.*?<\/div>/s', '', $rawBody);
            $rawBody = preg_replace('/<div class="overlay.*?<\/div>/s', '', $rawBody);
            $rawBody = preg_replace('/<div class="hero">.*?<\/div>\s*<\/div>\s*<\/div>/s', '', $rawBody);
            $rawBody = preg_replace('/<footer.*?<\/footer>/s', '', $rawBody);
            $content = trim($rawBody);
            echo "Matched 4\n";
        }
    }
} else {
    echo "File not found\n";
}

echo "Content length: " . strlen($content) . "\n";
echo "First 100 chars: " . substr($content, 0, 100) . "\n";
echo "Last 100 chars: " . substr($content, -100) . "\n";
