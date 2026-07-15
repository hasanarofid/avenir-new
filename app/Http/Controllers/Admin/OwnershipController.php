<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Jobs\ExtractKseiOwnershipJob;
use App\Jobs\ProcessOwnershipCsvJob;

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
            'file_daily_5pct' => 'required|file|mimes:xlsx,xls|max:50240', // Max 50MB
            'file_monthly_type' => 'required|file|mimes:xlsx,xls|max:50240',
            'file_monthly_classification' => 'required|file|mimes:xlsx,xls|max:50240',
            'file_monthly_1pct' => 'required|file|mimes:xlsx,xls|max:50240',
        ]);

        try {
            $daily5pctPath = $request->file('file_daily_5pct')->store('ownership_excel');
            $monthlyTypePath = $request->file('file_monthly_type')->store('ownership_excel');
            $monthlyClassificationPath = $request->file('file_monthly_classification')->store('ownership_excel');
            $monthly1pctPath = $request->file('file_monthly_1pct')->store('ownership_excel');
            
            // Store snapshot
            $snapshotId = DB::table('ownership_snapshots')->insertGetId([
                'period_date' => $request->date_current,
                'file_path' => null, // Will be updated by the Job to point to JSON
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Dispatch job to process the 4 Excel files
            ProcessOwnershipCsvJob::dispatch($snapshotId, $daily5pctPath, $monthlyTypePath, $monthlyClassificationPath, $monthly1pctPath);
            Log::info("ProcessOwnershipCsvJob Dispatched. Snapshot ID: $snapshotId");

            return back()->with('success', 'File Excel KSEI berhasil diunggah. Sistem sedang memproses data di latar belakang. Silakan refresh halaman dalam 1 menit.');
            
        } catch (\Exception $e) {
            Log::error("Error uploading ownership files: " . $e->getMessage());
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
                            ->whereNotNull('file_path')
                            ->orderBy('period_date', 'desc')
                            ->orderBy('id', 'desc')
                            ->first();

        // Find previous valid snapshot
        $prevSnapshot = null;
        if ($latestSnapshot) {
            $prevSnapshot = DB::table('ownership_snapshots')
                                ->whereNotNull('file_path')
                                ->where('id', '!=', $latestSnapshot->id)
                                ->orderBy('period_date', 'desc')
                                ->orderBy('id', 'desc')
                                ->first();
        }

        if (!$latestSnapshot || !Storage::disk('public')->exists($latestSnapshot->file_path)) {
            return response()->json([
                'stats' => [],
                'entities' => new \stdClass(),
                'edges' => [],
                'changes' => [],
                'audits' => new \stdClass(),
                'groups' => new \stdClass(),
                'shadow' => new \stdClass(),
                'institutions' => new \stdClass(),
                'govHoldings' => [],
                'investorSummaries' => new \stdClass(),
            ]);
        }

        $jsonContent = Storage::disk('public')->get($latestSnapshot->file_path);
        return response($jsonContent, 200, ['Content-Type' => 'application/json']);
    }
}
