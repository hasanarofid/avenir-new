<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/test', 'POST', [
    'symbol' => 'TLKM',
    'company_name' => 'PT Telkom Indonesia (Persero) Tbk'
]);
$file = new \Illuminate\Http\UploadedFile(
    '/home/hasanarofid/Downloads/1780050149175_original_TW-I-2026-FS-Konsolidasian-Telkom-Bahasa.pdf',
    '1780050149175_original_TW-I-2026-FS-Konsolidasian-Telkom-Bahasa.pdf',
    'application/pdf',
    null,
    true
);
$request->files->set('pdf_file', $file);

$controller = new App\Http\Controllers\Admin\TickerController();
$aiService = new App\Services\ChatGptService();

echo "Starting generateWithAI...\n";
$response = $controller->generateWithAI($request, $aiService);
echo "\nResponse Status: " . $response->getStatusCode() . "\n";
echo "Response Content: \n" . substr($response->getContent(), 0, 500) . "...\n";
