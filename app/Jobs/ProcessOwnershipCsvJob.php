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
    protected $fullPath;
    protected $movPath;
    protected $govPath;

    public function __construct($snapshotId, $fullPath, $movPath, $govPath)
    {
        $this->snapshotId = $snapshotId;
        $this->fullPath = $fullPath;
        $this->movPath = $movPath;
        $this->govPath = $govPath;
    }

    public function handle(): void
    {
        Log::info("ProcessOwnershipCsvJob started for snapshot {$this->snapshotId}");

        $snapshot = DB::table('ownership_snapshots')->find($this->snapshotId);
        if (!$snapshot) {
            Log::error("Snapshot {$this->snapshotId} not found.");
            return;
        }

        $fullCsv = storage_path('app/private/' . $this->fullPath);
        $movCsv = storage_path('app/private/' . $this->movPath);
        $govCsv = storage_path('app/private/' . $this->govPath);
        
        $scriptPath = base_path('scripts/build_ownership_data.py');
        $command = escapeshellcmd("python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($fullCsv) . " " . escapeshellarg($movCsv) . " " . escapeshellarg($govCsv));
        
        Log::info("Running command: $command");
        $output = shell_exec($command);

        if (!$output) {
            Log::error("Python script returned no output.");
            return;
        }

        $result = json_decode($output, true);
        if (!$result || !isset($result['status']) || $result['status'] !== 'success') {
            Log::error("Python script failed. Output: " . substr($output, 0, 500));
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
    }
}
