<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EodUpload;
use App\Imports\StockSummaryImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class EodUploadController extends Controller
{
    public function index(Request $request)
    {
        $query = EodUpload::with('user')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('filename', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhere('trading_date', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $uploads = $query->paginate(10)->withQueryString();

        return inertia('Admin/EodUpload/Index', [
            'uploads' => $uploads,
            'filters' => $request->only('search', 'status'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $files = $request->file('files');
        $processedCount = 0;
        $failedFiles = [];

        foreach ($files as $file) {
            $filename = $file->getClientOriginalName();
            
            // Extract date YYYYMMDD from filename
            if (preg_match('/\d{8}/', $filename, $matches)) {
                $dateString = $matches[0];
                // Convert YYYYMMDD to Y-m-d
                $tradingDate = substr($dateString, 0, 4) . '-' . substr($dateString, 4, 2) . '-' . substr($dateString, 6, 2);
            } else {
                $failedFiles[] = $filename . ' (Tidak ada format tanggal YYYYMMDD pada nama file)';
                continue;
            }
            
            $path = $file->store('eod-uploads');

            $upload = EodUpload::create([
                'user_id' => auth()->id(),
                'filename' => $filename,
                'file_path' => $path,
                'trading_date' => $tradingDate,
                'status' => 'processing',
            ]);

            Excel::import(new StockSummaryImport($upload), $path);
            $processedCount++;
        }

        if (count($failedFiles) > 0) {
            return back()->with('success', "$processedCount file sedang diproses. Gagal memproses " . count($failedFiles) . " file: " . implode(', ', $failedFiles));
        }

        return back()->with('success', "$processedCount file EOD berhasil diunggah dan sedang diproses di latar belakang.");
    }

    public function update(Request $request, $id)
    {
        $upload = EodUpload::findOrFail($id);

        $request->validate([
            'trading_date' => 'required|date',
            'status' => 'required|string|in:pending,processing,completed,failed',
        ]);

        $upload->update([
            'trading_date' => $request->trading_date,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Data riwayat EOD Upload berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $upload = EodUpload::findOrFail($id);

        if ($upload->file_path && Storage::exists($upload->file_path)) {
            Storage::delete($upload->file_path);
        }

        $upload->delete();

        return back()->with('success', 'Riwayat upload EOD berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:eod_uploads,id',
        ]);

        $uploads = EodUpload::whereIn('id', $request->ids)->get();

        foreach ($uploads as $upload) {
            if ($upload->file_path && Storage::exists($upload->file_path)) {
                Storage::delete($upload->file_path);
            }
            $upload->delete();
        }

        return back()->with('success', count($request->ids) . ' riwayat upload EOD berhasil dihapus.');
    }

    public function reprocess($id)
    {
        $upload = EodUpload::findOrFail($id);

        if (!$upload->file_path || !Storage::exists($upload->file_path)) {
            return back()->with('error', 'File fisik Excel tidak ditemukan di server.');
        }

        $upload->update([
            'status' => 'processing',
            'error_message' => null,
            'processed_rows' => 0,
        ]);

        Excel::import(new StockSummaryImport($upload), $upload->file_path);

        return back()->with('success', 'Proses ulang file EOD berhasil dijalankan.');
    }
}
