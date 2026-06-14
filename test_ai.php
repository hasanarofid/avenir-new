<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/test', 'POST', ['symbol' => 'TLKM', 'company_name' => 'PT Telkom Indonesia']);
$controller = new App\Http\Controllers\Admin\TickerController();
$aiService = new App\Services\ChatGptService();
$response = $controller->generateWithAI($request, $aiService);
echo $response->getContent();
