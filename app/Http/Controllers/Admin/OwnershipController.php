<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Jobs\ExtractKseiOwnershipJob;

class OwnershipController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Ownership/Index', [
            'recentSnapshots' => DB::table('ownership_snapshots')
                                    ->orderBy('period_date', 'desc')
                                    ->take(5)
                                    ->get()
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'date_current' => 'required|date',
            'file_current' => 'required|file|mimes:pdf,csv,xlsx,xls|max:10240', // Max 10MB
            'date_previous' => 'nullable|date',
            'file_previous' => 'nullable|file|mimes:pdf,csv,xlsx,xls|max:10240',
        ]);

        try {
            $currentPath = $request->file('file_current')->store('ownership_pdfs');
            
            $previousPath = null;
            if ($request->hasFile('file_previous')) {
                $previousPath = $request->file('file_previous')->store('ownership_pdfs');
            }

            // Store snapshot
            $snapshotId = DB::table('ownership_snapshots')->insertGetId([
                'period_date' => $request->date_current,
                'file_path' => $currentPath,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $previousSnapshotId = null;
            if ($previousPath) {
                // Determine if we need to link a previous snapshot
                // For simplicity, we can create a temporary snapshot record for the previous file
                $previousSnapshotId = DB::table('ownership_snapshots')->insertGetId([
                    'period_date' => $request->date_previous,
                    'file_path' => $previousPath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Dispatch jobs to parse the PDF using python script
            // If previous file exists, we must chain them to ensure previous is processed first before current calculates deltas
            if ($previousSnapshotId) {
                \Illuminate\Support\Facades\Bus::chain([
                    new ExtractKseiOwnershipJob($previousSnapshotId, null),
                    new ExtractKseiOwnershipJob($snapshotId, $previousSnapshotId)
                ])->dispatch();
                Log::info("Chained ExtractKseiOwnershipJob Dispatched. Snapshot IDs: $previousSnapshotId then $snapshotId");
            } else {
                ExtractKseiOwnershipJob::dispatch($snapshotId, null);
                Log::info("ExtractKseiOwnershipJob Dispatched. Snapshot ID: $snapshotId");
            }

            return back()->with('success', 'File berhasil diunggah. Proses ekstraksi data berjalan di latar belakang (Mendukung PDF KSEI Konsisten & CSV). Anda dapat merefresh halaman dalam 1-2 menit untuk melihat Snapshot.');
            
        } catch (\Exception $e) {
            Log::error("Error uploading ownership file: " . $e->getMessage());
            return back()->withErrors(['message' => 'Terjadi kesalahan saat mengunggah file.']);
        }
    }

    public function destroy($id)
    {
        $snapshot = DB::table('ownership_snapshots')->find($id);
        if (!$snapshot) {
            return back()->withErrors(['message' => 'Snapshot tidak ditemukan.']);
        }
        
        DB::beginTransaction();
        try {
            DB::table('ownership_edges')->where('snapshot_id', $id)->delete();
            DB::table('ownership_changes')->where('snapshot_id', $id)->delete();
            DB::table('ownership_audits')->where('snapshot_id', $id)->delete();
            DB::table('ownership_snapshots')->where('id', $id)->delete();
            DB::commit();
            
            // Delete file
            if ($snapshot->file_path && file_exists(storage_path('app/' . $snapshot->file_path))) {
                @unlink(storage_path('app/' . $snapshot->file_path));
            } elseif ($snapshot->file_path && file_exists(storage_path('app/private/' . $snapshot->file_path))) {
                @unlink(storage_path('app/private/' . $snapshot->file_path));
            }
            
            return back()->with('success', 'Snapshot berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting snapshot: " . $e->getMessage());
            return back()->withErrors(['message' => 'Gagal menghapus snapshot.']);
        }
    }

    public function getOwnershipData()
    {
        // Find latest valid snapshot
        $latestSnapshot = DB::table('ownership_snapshots')
                            ->whereExists(function ($query) {
                                $query->select(DB::raw(1))
                                      ->from('ownership_edges')
                                      ->whereColumn('ownership_edges.snapshot_id', 'ownership_snapshots.id');
                            })
                            ->orderBy('period_date', 'desc')
                            ->orderBy('id', 'desc')
                            ->first();

        // Find previous valid snapshot
        $prevSnapshot = null;
        if ($latestSnapshot) {
            $prevSnapshot = DB::table('ownership_snapshots')
                                ->whereExists(function ($query) {
                                    $query->select(DB::raw(1))
                                          ->from('ownership_edges')
                                          ->whereColumn('ownership_edges.snapshot_id', 'ownership_snapshots.id');
                                })
                                ->where('id', '!=', $latestSnapshot->id)
                                ->orderBy('period_date', 'desc')
                                ->orderBy('id', 'desc')
                                ->first();
        }

        if (!$latestSnapshot) {
            return response()->json([
                'stats' => [],
                'entities' => new \stdClass(),
                'edges' => [],
                'changes' => [],
                'audits' => new \stdClass(),
                'investorSummaries' => new \stdClass(),
            ]);
        }

        $sid = $latestSnapshot->id;

        // Entities
        $entitiesRaw = DB::table('ownership_entities')->get();
        $entities = [];
        foreach ($entitiesRaw as $e) {
            $entities[$e->key] = [
                'key' => $e->key,
                'label' => $e->label,
                'ticker' => $e->ticker,
                'kind' => $e->kind,
                'norm' => $e->norm,
                'listed' => (bool) $e->listed,
                'alsoInvestor' => (bool) $e->also_investor,
            ];
        }

        // Edges
        $edgesRaw = DB::table('ownership_edges')->where('snapshot_id', $sid)->get();
        $edges = [];
        foreach ($edgesRaw as $e) {
            $edges[] = [
                'from' => $e->from_key,
                'to' => $e->to_key,
                'ticker' => $e->ticker,
                'issuer' => $e->issuer_name,
                'investor' => $e->investor_name,
                'pct' => (float) $e->pct,
                'shares' => (int) $e->shares,
                'direction' => $e->direction,
                'deltaShares' => (int) $e->delta_shares,
                'deltaPct' => (float) $e->delta_pct,
                'local_foreign' => $e->local_foreign,
            ];
        }

        // Changes
        $changesRaw = DB::table('ownership_changes')->where('snapshot_id', $sid)->get();
        $changes = [];
        foreach ($changesRaw as $c) {
            $changes[] = [
                'from' => $c->from_key,
                'to' => $c->to_key,
                'investor' => $c->investor_name,
                'ticker' => $c->ticker,
                'direction' => $c->direction,
                'deltaShares' => (int) $c->delta_shares,
                'deltaPct' => (float) $c->delta_pct,
            ];
        }

        // Audits
        $auditsRaw = DB::table('ownership_audits')->where('snapshot_id', $sid)->get();
        $audits = [];
        foreach ($auditsRaw as $a) {
            $audits[$a->issuer_key] = [
                'confidence' => (int) $a->confidence,
                'top1' => (float) $a->top1,
                'hhi' => (float) $a->hhi,
                'nakamoto50' => (int) $a->nakamoto50,
                'residual' => (float) $a->residual,
                'floatProxy' => (float) $a->float_proxy,
                'controlLabel' => $a->control_label,
            ];
        }

        return response()->json([
            'stats' => [
                'latestDate' => $latestSnapshot->period_date,
                'prevDate' => $prevSnapshot ? $prevSnapshot->period_date : null,
                'issuersLatest' => count($audits),
                'defaultKey' => count($entities) > 0 ? (isset($entities['E:BREN']) ? 'E:BREN' : array_keys($entities)[0]) : null,
            ],
            'entities' => (object) $entities,
            'edges' => $edges,
            'changes' => $changes,
            'audits' => (object) $audits,
            'investorSummaries' => new \stdClass(),
        ]);
    }
}
