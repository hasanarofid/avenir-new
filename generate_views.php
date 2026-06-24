<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Check if research_view_logs is empty, if so, generate 500 fake views over last 30 days
if (DB::table('research_view_logs')->count() < 100) {
    $researchIds = DB::table('research')->pluck('id')->toArray();
    if (empty($researchIds)) {
        echo "No research found to attach views to.\n";
        exit;
    }
    
    $logs = [];
    for ($i = 0; $i < 500; $i++) {
        $daysAgo = rand(0, 29);
        $hour = rand(0, 23);
        $minute = rand(0, 59);
        $createdAt = Carbon::now()->subDays($daysAgo)->setHour($hour)->setMinute($minute)->setSecond(0);
        
        // Let's make some research more popular than others
        $rId = $researchIds[array_rand($researchIds)];
        if (rand(0, 2) === 0) {
            $rId = $researchIds[0]; // make the first one popular
        }
        
        $logs[] = [
            'research_id' => $rId,
            'user_id' => null,
            'ip_address' => '127.0.0.' . rand(1, 255),
            'created_at' => $createdAt
        ];
    }
    DB::table('research_view_logs')->insert($logs);
    echo "Inserted 500 dummy views to research_view_logs.\n";
} else {
    echo "Already has views.\n";
}
