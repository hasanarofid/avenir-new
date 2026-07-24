<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class MasterStockController extends Controller
{
    /**
     * Halaman admin untuk manage master_stocks.
     */
    public function index()
    {
        $stocks = DB::table('master_stocks')
            ->select('code', 'name', 'sector', 'sub_industry_code', 'sub_industry', 'is_sharia', 'logo_url', 'fiscal_year_end', 'fs_date')
            ->orderBy('sector')
            ->orderBy('code')
            ->get();

        $stats = [
            'total'   => $stocks->count(),
            'sectors' => DB::table('master_stocks')
                            ->select('sector', DB::raw('COUNT(*) as total'))
                            ->whereNotNull('sector')
                            ->groupBy('sector')
                            ->orderBy('total', 'desc')
                            ->get(),
        ];

        return inertia('Admin/MasterStock/Index', compact('stocks', 'stats'));
    }

    /**
     * Import master_stocks dari Financial Data Excel.
     * Pakai Python script untuk parsing, lalu upsert ke DB.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:20480',
        ]);

        try {
            $path = $request->file('file')->store('temp_master_stock', 'local');
            $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($path);

            $scriptPath = base_path('scripts/import_master_stocks.py');
            $command = escapeshellcmd("python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($fullPath));

            $output = shell_exec($command . ' 2>&1');

            if (!$output) {
                throw new \Exception('Python script returned no output.');
            }

            $result = json_decode($output, true);

            if (!$result || ($result['status'] ?? '') !== 'success') {
                throw new \Exception('Parse error: ' . ($result['message'] ?? substr($output, 0, 300)));
            }

            $records = $result['records'] ?? [];
            $now = now();
            $upserted = 0;

            // Batch upsert
            $chunks = array_chunk($records, 200);
            foreach ($chunks as $chunk) {
                foreach ($chunk as $r) {
                    DB::table('master_stocks')->updateOrInsert(
                        ['code' => $r['code']],
                        [
                            'name'              => $r['name'],
                            'sector'            => $r['sector'],
                            'sub_industry_code' => $r['sub_industry_code'],
                            'sub_industry'      => $r['sub_industry'],
                            'is_sharia'         => $r['is_sharia'],
                            'fs_date'           => $r['fs_date'],
                            'fiscal_year_end'   => $r['fiscal_year_end'],
                            'updated_at'        => $now,
                        ]
                    );
                    $upserted++;
                }
            }

            // Cleanup temp file
            Storage::disk('local')->delete($path);

            Log::info("MasterStock import: {$upserted} records upserted from Excel.");

            return back()->with('success', "Berhasil import {$upserted} emiten dari Excel. Sektoral: " . json_encode($result['sectors']));

        } catch (\Exception $e) {
            Log::error('MasterStock import error: ' . $e->getMessage());
            return back()->withErrors(['message' => 'Import gagal: ' . $e->getMessage()]);
        }
    }

    /**
     * Resolve logo URL untuk emiten dari Stockbit API/CDN (publik).
     * Endpoint IDX di-block oleh Cloudflare untuk hotlinking.
     * Logo: https://assets.stockbit.com/logos/companies/{TICKER}.png
     */
    public function syncLogos()
    {
        $stocks = DB::table('master_stocks')
            ->whereNull('logo_url')
            ->orWhere('logo_url', 'like', '%idx.co.id%') // Update also if previously IDX
            ->pluck('code');

        $updated = 0;
        foreach ($stocks as $code) {
            // Stockbit public CDN (No Cloudflare hotlink protection)
            $logoUrl = "https://assets.stockbit.com/logos/companies/{$code}.png";

            // Check if URL is valid (optional — bisa skip untuk hemat request)
            // Langsung set saja, handle 404 di frontend dengan fallback
            DB::table('master_stocks')
                ->where('code', $code)
                ->update([
                    'logo_url'   => $logoUrl,
                    'updated_at' => now(),
                ]);
            $updated++;
        }

        return back()->with('success', "Sync logo selesai: {$updated} emiten diupdate.");
    }

    /**
     * API endpoint: return master_stocks as JSON (for internal use).
     * Dipakai oleh ownership, desk-brief, dll yang butuh data sektor.
     */
    public function apiList(Request $request)
    {
        $query = DB::table('master_stocks')->select(
            'code', 'name', 'sector', 'sub_industry_code', 'sub_industry',
            'is_sharia', 'logo_url', 'fiscal_year_end', 'fs_date'
        );

        if ($sector = $request->query('sector')) {
            $query->where('sector', $sector);
        }
        if ($codes = $request->query('codes')) {
            $query->whereIn('code', explode(',', $codes));
        }

        return response()->json($query->orderBy('code')->get()->keyBy('code'));
    }

    /**
     * Update satu record emiten.
     */
    public function update(Request $request, string $code)
    {
        $data = $request->validate([
            'name'              => 'nullable|string|max:255',
            'sector'            => 'nullable|string|max:100',
            'sub_industry_code' => 'nullable|string|max:10',
            'sub_industry'      => 'nullable|string|max:255',
            'is_sharia'         => 'boolean',
            'logo_url'          => 'nullable|url|max:500',
            'fs_date'           => 'nullable|string|max:20',
            'fiscal_year_end'   => 'nullable|string|max:10',
        ]);

        $updated = DB::table('master_stocks')
            ->where('code', $code)
            ->update(array_merge($data, ['updated_at' => now()]));

        if (!$updated) {
            return back()->withErrors(['message' => "Kode {$code} tidak ditemukan."]);
        }

        return back()->with('success', "Emiten {$code} berhasil diperbarui.");
    }

    /**
     * Hapus satu record emiten.
     */
    public function destroy(string $code)
    {
        DB::table('master_stocks')->where('code', $code)->delete();

        return back()->with('success', "Emiten {$code} berhasil dihapus.");
    }
}

