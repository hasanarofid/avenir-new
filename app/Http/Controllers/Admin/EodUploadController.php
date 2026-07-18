<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EodUpload;
use App\Imports\StockSummaryImport;
use Maatwebsite\Excel\Facades\Excel;

class EodUploadController extends Controller
{
    public function index()
    {
        $uploads = EodUpload::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return inertia('Admin/EodUpload/Index', [
            'uploads' => $uploads
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
                $failedFiles[] = $filename . ' (Tidak ada angka YYYYMMDD)';
                continue;
            }
            
            $path = $file->store('eod-uploads');

            $upload = EodUpload::create([
                'user_id' => auth()->id(),
                'filename' => $filename,
                'trading_date' => $tradingDate,
                'status' => 'processing',
            ]);

            Excel::import(new StockSummaryImport($upload), $path);
            $processedCount++;
        }

        if (count($failedFiles) > 0) {
            return back()->with('success', "$processedCount file(s) sedang diproses. Gagal memproses " . count($failedFiles) . " file: " . implode(', ', $failedFiles));
        }

        return back()->with('success', "$processedCount file(s) is being processed in the background.");
    }
}
