<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExtractKseiOwnershipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $currentSnapshotId;
    protected $previousSnapshotId;

    public $timeout = 600; // 10 minutes max execution time

    public function __construct($currentSnapshotId, $previousSnapshotId = null)
    {
        $this->currentSnapshotId = $currentSnapshotId;
        $this->previousSnapshotId = $previousSnapshotId;
    }

    public function handle(): void
    {
        Log::info("ExtractKseiOwnershipJob started for snapshot {$this->currentSnapshotId}");

        $currentSnapshot = DB::table('ownership_snapshots')->find($this->currentSnapshotId);
        if (!$currentSnapshot) {
            Log::error("Snapshot {$this->currentSnapshotId} not found.");
            return;
        }

        // 1. Run Python Parser for Current Snapshot
        $filePath = storage_path('app/public/' . $currentSnapshot->file_path);
        
        // If it's a CSV, we should handle it differently. But for now, we assume the user uploaded the KSEI PDF.
        // Or if it's CSV, the python script or PHP could parse it directly. 
        // For simplicity, we'll try the python script. If it fails, we abort.
        if (str_ends_with(strtolower($filePath), '.csv')) {
            Log::info("CSV parsing not yet fully implemented in python script. Assuming PDF for now.");
        }

        $scriptPath = base_path('scripts/parse_ksei.py');
        $command = escapeshellcmd("python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($filePath));
        
        Log::info("Running command: $command");
        $output = shell_exec($command);

        if (!$output) {
            Log::error("Python script returned no output.");
            return;
        }

        $result = json_decode($output, true);

        if (!$result || !isset($result['status']) || $result['status'] !== 'success') {
            Log::error("Failed to parse KSEI PDF: " . ($result['error'] ?? 'Unknown error'));
            return;
        }

        $records = $result['data'];
        Log::info("Extracted " . count($records) . " records from PDF.");

        // 2. Insert Data into DB
        DB::beginTransaction();
        try {
            // Delete old edges for this snapshot just in case it's a retry
            DB::table('ownership_edges')->where('snapshot_id', $this->currentSnapshotId)->delete();

            foreach ($records as $record) {
                $ticker = $record['ticker'];
                $investorName = $record['investor_name'];
                $shares = $record['shares'];
                $pct = $record['pct'];
                $lf = $record['local_foreign'];

                $issuerKey = "E:" . $ticker;
                
                // Create or update Issuer Entity
                DB::table('ownership_entities')->updateOrInsert(
                    ['key' => $issuerKey],
                    [
                        'label' => $ticker . ' (Issuer)',
                        'ticker' => $ticker,
                        'kind' => 'issuer',
                        'listed' => true,
                        'updated_at' => now(),
                    ]
                );

                // Create or update Investor Entity
                // We generate a deterministic key based on the sanitized investor name
                $investorClean = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($investorName));
                $investorKey = "I:" . substr($investorClean, 0, 20);

                DB::table('ownership_entities')->updateOrInsert(
                    ['key' => $investorKey],
                    [
                        'label' => $investorName,
                        'kind' => 'investor',
                        'norm' => $investorClean,
                        'updated_at' => now(),
                    ]
                );

                // Insert Edge
                DB::table('ownership_edges')->insert([
                    'snapshot_id' => $this->currentSnapshotId,
                    'from_key' => $investorKey,
                    'to_key' => $issuerKey,
                    'ticker' => $ticker,
                    'issuer_name' => $ticker,
                    'investor_name' => $investorName,
                    'investor_raw' => $investorName,
                    'shares' => $shares,
                    'pct' => $pct,
                    'local_foreign' => $lf,
                    'direction' => 'NEW', // Default, will update during Delta calculation
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 3. Calculate Deltas if previous snapshot is provided
            if ($this->previousSnapshotId) {
                $this->calculateDeltas($this->currentSnapshotId, $this->previousSnapshotId);
            }

            // 4. Calculate Audits (Metrical data)
            $this->calculateAudits($this->currentSnapshotId);

            DB::commit();
            Log::info("KSEI Ownership Extraction completed for snapshot {$this->currentSnapshotId}.");

            // Auto-delete the file to save server storage
            if (file_exists($filePath)) {
                unlink($filePath);
                Log::info("Deleted processed file: {$filePath}");
            }
            // If there's a previous snapshot, delete that file too
            if ($this->previousSnapshotId) {
                $prevSnapshot = DB::table('ownership_snapshots')->find($this->previousSnapshotId);
                if ($prevSnapshot && $prevSnapshot->file_path) {
                    $prevPath = storage_path('app/public/' . $prevSnapshot->file_path);
                    if (file_exists($prevPath)) {
                        unlink($prevPath);
                        Log::info("Deleted processed previous file: {$prevPath}");
                    }
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saving ownership data: " . $e->getMessage() . " at " . $e->getLine());
        }
    }

    protected function calculateDeltas($currentSnapshotId, $previousSnapshotId)
    {
        Log::info("Calculating deltas between $currentSnapshotId and $previousSnapshotId");
        
        DB::table('ownership_changes')->where('snapshot_id', $currentSnapshotId)->delete();

        // Get all edges for both snapshots
        $currentEdges = DB::table('ownership_edges')
            ->where('snapshot_id', $currentSnapshotId)
            ->get()
            ->keyBy(function ($item) {
                return $item->from_key . '-' . $item->to_key;
            });

        $previousEdges = DB::table('ownership_edges')
            ->where('snapshot_id', $previousSnapshotId)
            ->get()
            ->keyBy(function ($item) {
                return $item->from_key . '-' . $item->to_key;
            });

        $changes = [];

        // Check for updates, new positions
        foreach ($currentEdges as $key => $curr) {
            if ($previousEdges->has($key)) {
                $prev = $previousEdges->get($key);
                $deltaShares = $curr->shares - $prev->shares;
                $deltaPct = $curr->pct - $prev->pct;

                $direction = 'UNCHANGED';
                if ($deltaShares > 0) $direction = 'BUY';
                elseif ($deltaShares < 0) $direction = 'SELL';

                if ($direction !== 'UNCHANGED') {
                    $changes[] = [
                        'snapshot_id' => $currentSnapshotId,
                        'from_key' => $curr->from_key,
                        'to_key' => $curr->to_key,
                        'investor_name' => $curr->investor_name,
                        'ticker' => $curr->ticker,
                        'direction' => $direction,
                        'delta_shares' => $deltaShares,
                        'delta_pct' => $deltaPct,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    DB::table('ownership_edges')->where('id', $curr->id)->update([
                        'direction' => $direction,
                        'delta_shares' => $deltaShares,
                        'delta_pct' => $deltaPct,
                    ]);
                }
            } else {
                // New position
                $changes[] = [
                    'snapshot_id' => $currentSnapshotId,
                    'from_key' => $curr->from_key,
                    'to_key' => $curr->to_key,
                    'investor_name' => $curr->investor_name,
                    'ticker' => $curr->ticker,
                    'direction' => 'NEW',
                    'delta_shares' => $curr->shares,
                    'delta_pct' => $curr->pct,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                DB::table('ownership_edges')->where('id', $curr->id)->update([
                    'direction' => 'NEW',
                    'delta_shares' => $curr->shares,
                    'delta_pct' => $curr->pct,
                ]);
            }
        }

        // Check for exits
        foreach ($previousEdges as $key => $prev) {
            if (!$currentEdges->has($key)) {
                $changes[] = [
                    'snapshot_id' => $currentSnapshotId,
                    'from_key' => $prev->from_key,
                    'to_key' => $prev->to_key,
                    'investor_name' => $prev->investor_name,
                    'ticker' => $prev->ticker,
                    'direction' => 'EXIT',
                    'delta_shares' => -$prev->shares,
                    'delta_pct' => -$prev->pct,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                // Note: EXIT is only recorded in ownership_changes since the edge doesn't exist in current snapshot
            }
        }

        if (count($changes) > 0) {
            // Chunk inserts to avoid query too large
            $chunks = array_chunk($changes, 500);
            foreach ($chunks as $chunk) {
                DB::table('ownership_changes')->insert($chunk);
            }
        }
        
        Log::info("Delta calculation finished. " . count($changes) . " changes recorded.");
    }

    protected function calculateAudits($currentSnapshotId)
    {
        Log::info("Calculating audits (HHI, Top1, etc) for snapshot $currentSnapshotId");
        
        DB::table('ownership_audits')->where('snapshot_id', $currentSnapshotId)->delete();

        $issuers = DB::table('ownership_edges')
            ->select('to_key', 'ticker')
            ->where('snapshot_id', $currentSnapshotId)
            ->distinct()
            ->get();

        $audits = [];
        foreach ($issuers as $issuer) {
            $edges = DB::table('ownership_edges')
                ->where('snapshot_id', $currentSnapshotId)
                ->where('to_key', $issuer->to_key)
                ->orderByDesc('pct')
                ->get();

            if ($edges->isEmpty()) continue;

            $top1 = $edges->first()->pct;
            $hhi = 0;
            $sumPct = 0;
            $nakamoto = 0;
            $nakamotoSum = 0;
            
            foreach ($edges as $edge) {
                $pct = $edge->pct;
                $hhi += ($pct * $pct);
                $sumPct += $pct;
                
                if ($nakamotoSum < 50) {
                    $nakamotoSum += $pct;
                    $nakamoto++;
                }
            }

            $residual = max(0, 100 - $sumPct);

            $controlLabel = 'Unknown';
            if ($top1 >= 50) $controlLabel = 'Absolute Control';
            elseif ($top1 >= 25) $controlLabel = 'Effective Control';
            else $controlLabel = 'Dispersed';

            $audits[] = [
                'snapshot_id' => $currentSnapshotId,
                'issuer_key' => $issuer->to_key,
                'confidence' => 80, // estimated
                'top1' => $top1,
                'control_label' => $controlLabel,
                'hhi' => $hhi,
                'nakamoto50' => $nakamoto,
                'residual' => $residual,
                'float_proxy' => $residual,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (count($audits) > 0) {
            $chunks = array_chunk($audits, 500);
            foreach ($chunks as $chunk) {
                DB::table('ownership_audits')->insert($chunk);
            }
        }
    }
}
