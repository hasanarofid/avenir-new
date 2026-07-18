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
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            'trading_date' => 'required|date',
        ]);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        
        // Save the file temporarily or permanently
        $path = $file->store('eod-uploads');

        // Estimate total rows (optional, or just set to 0 for now)
        $upload = EodUpload::create([
            'user_id' => auth()->id(),
            'filename' => $filename,
            'trading_date' => $request->trading_date,
            'status' => 'processing',
        ]);

        Excel::import(new StockSummaryImport($upload), $path);

        return back()->with('success', 'File is being processed in the background.');
    }
}
