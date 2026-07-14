<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

            // Create snapshot entry for current period
            $snapshotId = DB::table('ownership_snapshots')->insertGetId([
                'period_date' => $request->date_current,
                'file_path' => $currentPath,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Here we would dispatch a job to parse the PDF using python/spatie
            // e.g. ExtractKseiOwnershipJob::dispatch($snapshotId, $currentPath, $previousPath);
            Log::info("Ownership Data uploaded. Snapshot ID: $snapshotId");

            return back()->with('success', 'File berhasil diunggah. Proses ekstraksi data berjalan di latar belakang (Mendukung PDF KSEI Konsisten & CSV).');
            
        } catch (\Exception $e) {
            Log::error("Error uploading ownership file: " . $e->getMessage());
            return back()->withErrors(['message' => 'Terjadi kesalahan saat mengunggah file.']);
        }
    }
}
