<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PoolController extends Controller
{
    public function index()
    {
        $currentYear = now()->year;
        
        $pools = DB::table('pool_config')
            ->orderBy('period_year', 'desc')
            ->orderBy('period_month', 'desc')
            ->get();

        return Inertia::render('Admin/Pool/Index', [
            'pools' => $pools,
            'currentYear' => $currentYear,
            'currentMonth' => now()->month,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'period_year' => 'required|integer',
            'period_month' => 'required|integer|min:1|max:12',
            'pool_budget_idr' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $data['updated_at'] = now();
        $data['updated_by'] = auth()->id();

        DB::table('pool_config')->updateOrInsert(
            [
                'period_year' => $data['period_year'],
                'period_month' => $data['period_month']
            ],
            $data
        );

        return redirect()->back()->with('success', 'Konfigurasi Pool berhasil disimpan');
    }
}
