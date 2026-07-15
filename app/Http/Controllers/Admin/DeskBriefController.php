<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeskBrief;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeskBriefController extends Controller
{
    public function index()
    {
        $deskBriefs = DeskBrief::with(['analyst', 'marketStance'])->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);
            
        return Inertia::render('Admin/DeskBrief/Index', [
            'deskBriefs' => $deskBriefs
        ]);
    }

    public function edit($id)
    {
        $deskBrief = DeskBrief::with('marketStance')->findOrFail($id);
        
        return Inertia::render('Admin/DeskBrief/Edit', [
            'deskBrief' => $deskBrief
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'market_read' => 'nullable|string',
            'so_what' => 'nullable|string',
            'what_to_do' => 'nullable|string',
            'momentum_score' => 'nullable|numeric|min:0|max:100',
            'breadth_score' => 'nullable|numeric|min:0|max:100',
            'foreign_score' => 'nullable|numeric|min:0|max:100',
            'sector_score' => 'nullable|numeric|min:0|max:100',
            'rupiah_score' => 'nullable|numeric|min:0|max:100',
        ]);

        $deskBrief = DeskBrief::findOrFail($id);
        
        $deskBrief->update([
            'title' => $request->title,
            'market_read' => $request->market_read,
            'so_what' => $request->so_what,
            'what_to_do' => $request->what_to_do,
        ]);

        if ($deskBrief->market_stance_id && $request->has('momentum_score')) {
            $stance = \App\Models\MarketStanceDaily::find($deskBrief->market_stance_id);
            if ($stance) {
                $stance->momentum_score = $request->momentum_score;
                $stance->breadth_score = $request->breadth_score;
                $stance->foreign_score = $request->foreign_score;
                $stance->sector_score = $request->sector_score;
                $stance->rupiah_score = $request->rupiah_score;
                
                // Recalculate Total Score
                $totalScore = ($request->momentum_score * 0.3) +
                              ($request->breadth_score * 0.25) +
                              ($request->foreign_score * 0.2) +
                              ($request->sector_score * 0.15) +
                              ($request->rupiah_score * 0.1);
                              
                $stance->score = round($totalScore);
                $stance->save();
            }
        }

        return redirect()->route('admin.desk-brief.index')->with('success', 'Desk Brief updated successfully.');
    }

    public function destroy($id)
    {
        $deskBrief = DeskBrief::findOrFail($id);
        
        if ($deskBrief->market_stance_id) {
            \App\Models\MarketStanceDaily::where('id', $deskBrief->market_stance_id)->delete();
        }
        
        $deskBrief->delete();

        return redirect()->route('admin.desk-brief.index')->with('success', 'Desk Brief deleted successfully.');
    }

    public function publish($id)
    {
        $deskBrief = DeskBrief::findOrFail($id);
        
        $deskBrief->update([
            'status' => 'published',
            'published_at' => now(),
            'analyst_id' => auth()->id()
        ]);

        return redirect()->route('admin.desk-brief.index')->with('success', 'Desk Brief published successfully.');
    }

    public function runTester(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);
        
        $date = \Carbon\Carbon::parse($request->date)->toDateString();
        
        // 1. Calculate Key Drivers (to get flow & breadth components)
        $keyDriversEngine = app(\App\Services\MarketIntelligence\KeyDriversEngine::class);
        
        $usdIdrProxy = \App\Models\MarketSnapshot::where('date', '<=', $date)
            ->where('symbol_or_metric', 'USD_IDR_PROXY')
            ->orderBy('date', 'desc')
            ->first();

        $manualInputs = [
            'RUPIAH_BI_SBN_YIELD' => [
                'usd_idr_change_5d' => $usdIdrProxy ? (float) $usdIdrProxy->change_pct : null,
                'sbn_10y' => null,
                'sbn_10y_change_5d' => null,
                'bi_stance' => 'neutral',
            ]
        ];

        $driversData = $keyDriversEngine->buildIhsgKeyDrivers('LQ45', 5, $date, $manualInputs);
        
        $scoringEngine = app(\App\Services\MarketIntelligence\ScoringEngine::class);
        
        try {
            $gathered = $scoringEngine->gatherMarketData($date, $driversData);
            $marketData = $gathered['marketData'];
            $latestDate = $gathered['latestDate'];
            $result = $scoringEngine->calculateMarketRegime($marketData);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 400);
        }
        
        return response()->json([
            'date' => $date,
            'latest_available_date' => $latestDate,
            'raw_data' => $marketData,
            'scores' => $result['component_scores'],
            'final_score' => $result['final_score'],
            'regime' => $result['regime'],
        ]);
    }

    public function uploadPdf(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:10240'
        ]);

        try {
            $path = $request->file('pdf_file')->store('idx_pdfs');
            $fullPath = \Illuminate\Support\Facades\Storage::path($path);

            $scriptPath = base_path('scripts/python/parse_idx_pdf.py');
            
            $process = new \Symfony\Component\Process\Process(['python3', $scriptPath, $fullPath]);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \Exception('Python Script Error: ' . ($process->getErrorOutput() ?: 'Command failed without error output.'));
            }

            $output = $process->getOutput();
            $parsed = json_decode($output, true);

            if (!$parsed || isset($parsed['error'])) {
                return back()->withErrors(['pdf_file' => 'Gagal memproses PDF: ' . ($parsed['error'] ?? 'Output bukan JSON yang valid. Output: ' . substr($output, 0, 100))]);
            }

            $date = \Carbon\Carbon::parse($parsed['date'])->toDateString();

            // Insert to MarketSnapshots
            $metrics = [
                'IHSG' => [
                    'value' => $parsed['ihsg_close'] ?? 0,
                    'change_abs' => $parsed['ihsg_change_abs'] ?? 0,
                    'change_pct' => $parsed['ihsg_change_pct'] ?? 0
                ],
                'VALUE_TRADED_BN_IDR' => [
                    'value' => $parsed['value_traded_bn_idr'] ?? 0
                ],
                'FOREIGN_NET_TODAY' => [
                    'value' => $parsed['foreign_net_today'] ?? 0
                ],
                'ADVANCERS' => [
                    'value' => $parsed['advancers'] ?? 0
                ],
                'DECLINERS' => [
                    'value' => $parsed['decliners'] ?? 0
                ],
                'STABLE' => [
                    'value' => $parsed['stable'] ?? 0
                ],
                'BREADTH_SCORE' => [
                    'value' => $parsed['breadth_score_pdf'] ?? $parsed['breadth_score'] ?? 0
                ]
            ];

            $pdfComponentKeys = [
                'PT_SCORE' => 'pt_score_pdf',
                'MB_SCORE' => 'breadth_score_pdf',
                'FLOW_SCORE' => 'flow_score_pdf',
                'SR_SCORE' => 'sr_score_pdf',
                'VS_SCORE' => 'vs_score_pdf'
            ];

            foreach ($pdfComponentKeys as $dbKey => $parsedKey) {
                if (isset($parsed[$parsedKey])) {
                    $metrics[$dbKey] = ['value' => $parsed[$parsedKey]];
                }
            }

            foreach ($metrics as $metric => $data) {
                if ($metric === 'BREADTH_SCORE') {
                    $existing = \App\Models\MarketSnapshot::where('date', $date)
                                ->where('symbol_or_metric', 'BREADTH_SCORE')
                                ->first();
                    if ($existing && $existing->source === 'ringkasan_saham') {
                        continue; // Skip overwriting higher quality stock-level score
                    }
                }

                \App\Models\MarketSnapshot::updateOrCreate(
                    ['date' => $date, 'symbol_or_metric' => $metric],
                    array_merge($data, ['source' => 'pdf_upload'])
                );
            }

            // Insert to SectorBiasDaily
            if (isset($parsed['sectors']) && is_array($parsed['sectors'])) {
                foreach ($parsed['sectors'] as $sectorName => $return1d) {
                    \App\Models\SectorBiasDaily::updateOrCreate(
                        ['date' => $date, 'sector' => $sectorName],
                        ['return_1d' => $return1d, 'bias' => 'neutral']
                    );
                }
            }

            // Generate Draft automatically
            \Illuminate\Support\Facades\Artisan::call('deskbrief:draft', [
                'date' => $date,
                '--force' => true
            ]);

            return back()->with('success', 'PDF berhasil diupload, data di-ekstrak, dan Draft berhasil dibuat untuk tanggal ' . $date);

        } catch (\Exception $e) {
            return back()->withErrors(['pdf_file' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }

    public function uploadIhsgCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        try {
            $path = $request->file('csv_file')->store('ihsg_csvs');
            $fullPath = \Illuminate\Support\Facades\Storage::path($path);

            $scriptPath = base_path('scripts/python/ihsg_price_trend.py');
            
            $process = new \Symfony\Component\Process\Process(['python3', $scriptPath, $fullPath]);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \Exception('Python Script Error: ' . ($process->getErrorOutput() ?: 'Command failed without error output.'));
            }

            $output = $process->getOutput();
            $parsed = json_decode($output, true);

            if (!$parsed || isset($parsed['error'])) {
                return back()->withErrors(['csv_file' => 'Gagal memproses CSV: ' . ($parsed['error'] ?? 'Output bukan JSON yang valid.')]);
            }
            
            $history = $parsed['history'] ?? (isset($parsed['date']) ? [$parsed] : []);
            $latest = $parsed['latest'] ?? $parsed;

            foreach ($history as $dataItem) {
                if (!isset($dataItem['date'])) continue;
                $itemDate = \Carbon\Carbon::parse($dataItem['date'])->toDateString();

                // Overwrite MarketSnapshot for IHSG
                if (isset($dataItem['prices_60d'])) {
                    \App\Models\MarketSnapshot::updateOrCreate(
                        ['date' => $itemDate, 'symbol_or_metric' => 'IHSG'],
                        [
                            'value' => $dataItem['close'],
                            'change_abs' => $dataItem['change_abs'],
                            'change_pct' => $dataItem['change_pct'],
                            'sparkline_json' => $dataItem['prices_60d'],
                            'source' => 'csv_upload'
                        ]
                    );
                }

                $metrics = [
                    'OPEN' => $dataItem['open'] ?? 0,
                    'HIGH' => $dataItem['high'] ?? 0,
                    'LOW' => $dataItem['low'] ?? 0,
                    'VOLATILITY_PERCENTILE' => $dataItem['volatility_percentile'] ?? null
                ];

                foreach ($metrics as $metricName => $metricValue) {
                    if ($metricValue !== null) {
                        \App\Models\MarketSnapshot::updateOrCreate(
                            ['date' => $itemDate, 'symbol_or_metric' => $metricName],
                            [
                                'value' => $metricValue,
                                'source' => 'csv_upload'
                            ]
                        );
                    }
                }

                // Update Momentum and Volatility Scores in MarketStanceDaily
                $stance = \App\Models\MarketStanceDaily::whereDate('date', $itemDate)->first();
                if ($stance) {
                    $stance->momentum_score = $dataItem['score'] ?? $stance->momentum_score;
                    if (isset($dataItem['volatility_score'])) {
                        $stance->rupiah_score = $dataItem['volatility_score'];
                    }
                    
                    // Recalculate Total Score
                    $totalScore = ($stance->momentum_score * 0.3) +
                                  ($stance->breadth_score * 0.25) +
                                  ($stance->foreign_score * 0.2) +
                                  ($stance->sector_score * 0.15) +
                                  ($stance->rupiah_score * 0.1);
                                  
                    $stance->score = round($totalScore);
                    $stance->save();
                }
                // DO NOT generate draft for historical data as it causes 504 Gateway Timeout
                // Drafts should only be generated for the latest day, or triggered manually/by PDF upload
            }

            $date = \Carbon\Carbon::parse($latest['date'])->toDateString();

            // Return the summary to be displayed on the frontend
            return back()->with('ihsg_trend_summary', $latest)->with('success', 'CSV historis berhasil diproses dan database hingga tanggal ' . $date . ' telah diperbarui.');

        } catch (\Exception $e) {
            return back()->withErrors(['csv_file' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }

    public function uploadMasterlist(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            $path     = $request->file('excel_file')->store('masterlist');
            $fullPath = \Illuminate\Support\Facades\Storage::path($path);

            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\MasterStockImport, $fullPath);

            // Regenerate sector_master.csv for Python sector_rotation.py
            $this->rebuildSectorMasterCsv($fullPath);

            return back()->with('success', 'Masterlist Saham berhasil diupdate!');
        } catch (\Exception $e) {
            return back()->withErrors(['excel_file' => 'Gagal mengupload Masterlist: ' . $e->getMessage()]);
        }
    }

    /**
     * Build sector_master.csv from Financial Data Excel for Python sector_rotation.py
     */
    private function rebuildSectorMasterCsv(string $xlsxPath): void
    {
        $script = <<<PYTHON
import openpyxl, csv, sys
wb = openpyxl.load_workbook(sys.argv[1], data_only=True)
ws = wb.active
out = sys.argv[2]
with open(out, 'w', newline='') as f:
    w = csv.writer(f)
    w.writerow(['code','sector'])
    for row in ws.iter_rows(min_row=6, values_only=True):
        code   = row[5]
        sector = row[2]
        if code and sector and str(code).strip() not in ['None','']:
            w.writerow([str(code).strip().upper(), str(sector).strip()])
PYTHON;

        $tmpScript = storage_path('app/rebuild_sector_master.py');
        file_put_contents($tmpScript, $script);
        $outCsv = storage_path('app/sector_master.csv');
        shell_exec("python3 " . escapeshellarg($tmpScript) . " " . escapeshellarg($xlsxPath) . " " . escapeshellarg($outCsv) . " 2>&1");
        @unlink($tmpScript);
    }



    public function uploadForeignFlow(Request $request)
    {
        $request->validate([
            'foreign_flow' => 'required|file|mimes:xlsx,xls|max:51200',
            'date'         => 'required|date'
        ]);

        try {
            $path     = $request->file('foreign_flow')->store('foreign_flow');
            $fullPath = \Illuminate\Support\Facades\Storage::path($path);
            $date     = \Carbon\Carbon::parse($request->date)->toDateString();

            $bridge = new \App\Services\MarketIntelligence\PythonBridge();
            $tempDir = $bridge->getTempDir();
            $outJson = $tempDir . "/ff_{$date}.json";
            
            $payload = $bridge->run('parse_foreign_flow.py', [
                '--input' => $fullPath,
                '--date'  => $date,
                '--output'=> $outJson
            ], $outJson);

            if (empty($payload)) {
                return back()->withErrors(['foreign_flow' => 'Gagal memproses Data Foreign Flow.']);
            }
            
            if (isset($payload['error'])) {
                return back()->withErrors(['foreign_flow' => $payload['error']]);
            }

            // payload is now a dictionary of { date_string => { FOREIGN_NET_TODAY, OPEN, HIGH, LOW, PRICE, VALUE_TRADED_BN_IDR } }
            // To properly calculate change and sparklines, we should sort the dates
            ksort($payload);
            
            $sparkline = [];
            $lastIhsg = null;

            foreach ($payload as $d => $metrics) {
                foreach ($metrics as $metric => $val) {
                    if ($metric === 'PRICE') {
                        $ihsg = \App\Models\MarketSnapshot::firstOrNew([
                            'date' => $d, 'symbol_or_metric' => 'IHSG'
                        ]);
                        $ihsg->value = $val;
                        
                        // Because we're iterating in chronological order, $lastIhsg is the previous trading day's data
                        if (!$lastIhsg) {
                            $lastIhsg = \App\Models\MarketSnapshot::where('symbol_or_metric', 'IHSG')
                                ->whereDate('date', '<', $d)
                                ->orderBy('date', 'desc')
                                ->first();
                            if ($lastIhsg && is_array($lastIhsg->sparkline_json)) {
                                $sparkline = $lastIhsg->sparkline_json;
                            }
                        }

                        if ($lastIhsg) {
                            $ihsg->change_abs = $ihsg->value - ($lastIhsg->value ?? $lastIhsg['value'] ?? 0);
                            $ihsg->change_pct = ($lastIhsg->value ?? $lastIhsg['value'] ?? 0) > 0 ? ($ihsg->change_abs / ($lastIhsg->value ?? $lastIhsg['value'])) : 0;
                        }
                        
                        $sparkline[] = (float) $ihsg->value;
                        if (count($sparkline) > 30) {
                            $sparkline = array_slice($sparkline, -30);
                        }
                        $ihsg->sparkline_json = $sparkline;
                        $ihsg->source = 'foreign_flow_excel';
                        $ihsg->save();
                        
                        $lastIhsg = $ihsg;
                    } else {
                        \App\Models\MarketSnapshot::updateOrCreate(
                            ['date' => $d, 'symbol_or_metric' => $metric],
                            ['value' => $val, 'source' => 'foreign_flow_excel']
                        );
                    }
                }
            }

            // Generate Draft automatically
            \Illuminate\Support\Facades\Artisan::call('deskbrief:draft', [
                'date' => $date,
                '--force' => true
            ]);

            return back()->with('success', 'Data Foreign Flow berhasil diupload & Draft otomatis diperbarui.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error($e);
            return back()->withErrors(['foreign_flow' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function uploadRingkasanSaham(Request $request)
    {
        $request->validate([
            'ringkasan_saham' => 'required|file|mimes:xlsx,xls,csv|max:51200',
            'index_summary'   => 'required|file|mimes:xlsx,xls,csv|max:51200',
            'date'            => 'required|date'
        ]);

        try {
            $path     = $request->file('ringkasan_saham')->store('ringkasan_saham');
            $fullPath = \Illuminate\Support\Facades\Storage::path($path);
            
            $indexPath = $request->file('index_summary')->store('index_summary');
            $indexFullPath = \Illuminate\Support\Facades\Storage::path($indexPath);

            $date     = \Carbon\Carbon::parse($request->date)->toDateString();

            // Find the latest uploaded masterlist (Financial Data) for sector mapping
            $masterPath = $this->findLatestMasterlist();

            // Run Python market_breadth.py + sector_rotation.py via BreadthService
            $breadthService = new \App\Services\MarketIntelligence\BreadthService(
                new \App\Services\MarketIntelligence\PythonBridge()
            );
            $results = $breadthService->calculateAndStore($date, $fullPath, $masterPath, $indexFullPath);

            if (empty($results)) {
                return back()->withErrors(['ringkasan_saham' => 'Gagal memproses Ringkasan Saham: Data tidak valid atau Python script gagal.']);
            }

            $mbPayload = $results['market_breadth'] ?? [];
            $srPayload = $results['sector_rotation'] ?? [];

            // Also update MarketStanceDaily breadth_score so dashboard reflects immediately
            $stance = \App\Models\MarketStanceDaily::firstOrNew(['date' => $date]);
            $stance->label          = $stance->label          ?? 'Unknown';
            $stance->momentum_score = $stance->momentum_score ?? 50;
            $stance->foreign_score  = $stance->foreign_score  ?? 50;
            $stance->sector_score   = isset($srPayload['sector_rotation_score']) ? (int)$srPayload['sector_rotation_score'] : ($stance->sector_score ?? 50);
            $stance->rupiah_score   = $stance->rupiah_score   ?? 50;
            $stance->breadth_score  = isset($mbPayload['market_breadth_score']) ? (int)$mbPayload['market_breadth_score'] : ($stance->breadth_score ?? 50);

            $totalScore = ($stance->momentum_score * 0.30) +
                          ($stance->breadth_score  * 0.25) +
                          ($stance->foreign_score  * 0.20) +
                          ($stance->sector_score   * 0.15) +
                          ($stance->rupiah_score   * 0.10);
            $stance->score = round($totalScore);
            $stance->save();

            $summary = array_merge($mbPayload, [
                'sector_rotation_score' => $srPayload['sector_rotation_score'] ?? null,
                'sector_rotation_label' => $srPayload['sector_rotation_label'] ?? null,
            ]);

            // Generate Draft automatically
            \Illuminate\Support\Facades\Artisan::call('deskbrief:draft', [
                'date' => $date,
                '--force' => true
            ]);

            return back()
                ->with('market_breadth_summary', $summary)
                ->with('success', 'File Ringkasan Saham berhasil diproses & Draft otomatis diperbarui.');

        } catch (\Exception $e) {
            return back()->withErrors(['ringkasan_saham' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }

    /**
     * Find the latest uploaded Financial Data masterlist for sector mapping.
     */
    private function findLatestMasterlist(): string
    {
        $latest = \App\Models\MarketSnapshot::where('symbol_or_metric', 'MASTERLIST_PATH')
            ->orderBy('date', 'desc')
            ->first();

        if ($latest && file_exists($latest->source)) {
            return $latest->source;
        }

        // Fallback: scan the ready-upload folder
        $readyUploadDir = '/home/hasanarofid/Documents/hasanarofid/avenir/ready-upload';
        $files = glob($readyUploadDir . '/Financial Data*.xlsx');
        if ($files) {
            return end($files);
        }

        return '';
    }
}
