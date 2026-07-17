<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessOwnershipCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    protected $snapshotId;
    protected $daily5pctPath;
    protected $monthlyTypePath;
    protected $monthlyClassificationPath;
    protected $monthly1pctPath;

    public function __construct($snapshotId, $daily5pctPath, $monthlyTypePath, $monthlyClassificationPath, $monthly1pctPath)
    {
        $this->snapshotId = $snapshotId;
        $this->daily5pctPath = $daily5pctPath;
        $this->monthlyTypePath = $monthlyTypePath;
        $this->monthlyClassificationPath = $monthlyClassificationPath;
        $this->monthly1pctPath = $monthly1pctPath;
    }

    public function handle(): void
    {
        Log::info("ProcessOwnershipCsvJob started for snapshot {$this->snapshotId}");

        $snapshot = DB::table('ownership_snapshots')->find($this->snapshotId);
        if (!$snapshot) {
            Log::error("Snapshot {$this->snapshotId} not found.");
            return;
        }

        $daily5pctFile = storage_path('app/private/' . $this->daily5pctPath);
        $monthlyTypeFile = storage_path('app/private/' . $this->monthlyTypePath);
        $monthlyClassificationFile = storage_path('app/private/' . $this->monthlyClassificationPath);
        $monthly1pctFile = storage_path('app/private/' . $this->monthly1pctPath);
        
        $brokers = \App\Models\Broker::pluck('name', 'code')->toArray();
        $brokersFile = storage_path('app/private/temp_brokers_' . uniqid() . '.json');
        file_put_contents($brokersFile, json_encode(['byCode' => $brokers]));

        $masterStocks = \App\Models\MasterStock::toMap();
        $masterStocksFile = storage_path('app/private/temp_master_stocks_' . uniqid() . '.json');
        file_put_contents($masterStocksFile, json_encode(['byCode' => $masterStocks]));

        $scriptPath = base_path('scripts/build_ownership_excel.py');
        $command = escapeshellcmd("python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($daily5pctFile) . " " . escapeshellarg($monthlyTypeFile) . " " . escapeshellarg($monthlyClassificationFile) . " " . escapeshellarg($monthly1pctFile) . " " . escapeshellarg($brokersFile) . " " . escapeshellarg($masterStocksFile));
        
        Log::info("Running command: $command");
        $output = shell_exec($command);

        if (!$output) {
            Log::error("Python script returned no output.");
            @unlink($brokersFile);
        @unlink($masterStocksFile);
            @unlink($masterStocksFile);
            return;
        }

        $result = json_decode($output, true);
        if (!$result || !isset($result['status']) || $result['status'] !== 'success') {
            Log::error("Python script failed. Output: " . substr($output, 0, 500));
            @unlink($brokersFile);
        @unlink($masterStocksFile);
            return;
        }

        // Save JSON file
        $jsonFileName = 'ownership_data_' . $snapshot->id . '.json';
        Storage::disk('public')->put('ownership_data/' . $jsonFileName, $output);
        
        // Update snapshot to point to this JSON
        DB::table('ownership_snapshots')
            ->where('id', $this->snapshotId)
            ->update([
                'file_path' => 'ownership_data/' . $jsonFileName,
                'updated_at' => now(),
            ]);

        Log::info("Successfully built JSON for snapshot {$this->snapshotId}");
        @unlink($brokersFile);
        @unlink($masterStocksFile);
    }
}
