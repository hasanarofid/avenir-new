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
        $soWhat = $request->so_what;

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
                              
                $newScore = (int) round($totalScore);
                $stance->score = $newScore;

                if ($newScore >= 75) $label = 'Risk-On Accumulation';
                elseif ($newScore >= 65) $label = 'Constructive Rotation';
                elseif ($newScore >= 55) $label = 'Neutral Rotation';
                elseif ($newScore >= 45) $label = 'Defensive Neutral';
                elseif ($newScore >= 35) $label = 'Risk-Off';
                else $label = 'Stress / Risk-Off Pressure';

                $stance->label = $label;
                $stance->save();

                // Auto-replace any hardcoded score in so_what text (e.g., "Regime score 63/100" -> "Regime score 60/100")
                if ($soWhat) {
                    $soWhat = preg_replace('/Regime score \d+\/100/i', "Regime score {$newScore}/100", $soWhat);
                    $soWhat = preg_replace('/Skor \d+\/100/i', "Skor {$newScore}/100", $soWhat);
                    $soWhat = preg_replace('/score \d+\/100/i', "score {$newScore}/100", $soWhat);
                }
            }
        }

        $deskBrief->update([
            'title' => $request->title,
            'market_read' => $request->market_read,
            'so_what' => $soWhat,
            'what_to_do' => $request->what_to_do,
        ]);

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
        
        $scoringEngine = app(\App\Services\MarketIntelligence\ScoringEngine::class);
        
        try {
            $pythonResult = $scoringEngine->runPythonScoring($date);
            $scores = $pythonResult['scores'];
            $payloads = $pythonResult['payloads'];
            
            $finalScore = $scoringEngine->calculateFinalRegimeScore($scores);
            $regime = $scoringEngine->classifyMarketRegime($scores, $finalScore);
            
            $marketData = [
                'close' => $payloads['price_trend']['close'] ?? 0,
                'ma20' => $payloads['price_trend']['ma20'] ?? 0,
                'ma60' => $payloads['price_trend']['ma60'] ?? 0,
                'ret_5d' => $payloads['price_trend']['ret_5d'] ?? 0,
                'ret_20d' => $payloads['price_trend']['ret_20d'] ?? 0,
                'drawdown_20d' => $payloads['price_trend']['drawdown_20d'] ?? 0,
                
                // Get breadth from snapshot if python payload is not available
                'advancers' => \App\Models\MarketSnapshot::where('symbol_or_metric', 'ADVANCERS')->whereDate('date', '<=', $date)->orderBy('date', 'desc')->value('value') ?? 0,
                'decliners' => \App\Models\MarketSnapshot::where('symbol_or_metric', 'DECLINERS')->whereDate('date', '<=', $date)->orderBy('date', 'desc')->value('value') ?? 0,
                
                'foreign_net_5d' => $payloads['flow']['foreign_net_5d'] ?? 0,
                'institutional_net_5d' => $payloads['flow']['institutional_net_5d'] ?? 0,
            ];
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 400);
        }
        
        return response()->json([
            'date' => $date,
            'latest_available_date' => $date,
            'raw_data' => $marketData,
            'scores' => $scores,
            'final_score' => $finalScore,
            'regime' => $regime,
        ]);
    }

    public function uploadPdf(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:10240'
        ]);

        try {
            $path = $request->file('pdf_file')->store('idx_pdfs', 'local');
            $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($path);

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
            $path = $request->file('csv_file')->store('ihsg_csvs', 'local');
            $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($path);

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
            $path     = $request->file('excel_file')->store('masterlist', 'local');
            $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($path);

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
            // Memperlonggar validasi mimes karena terkadang file .xlsx dari OS/Browser tertentu 
            // dibaca sebagai application/zip atau application/octet-stream oleh server.
            'foreign_flow' => 'required|file|max:51200',
            'date'         => 'required|date'
        ]);

        $extension = $request->file('foreign_flow')->getClientOriginalExtension();
        if (!in_array(strtolower($extension), ['xlsx', 'xls'])) {
            return back()->withErrors(['foreign_flow' => 'File harus berupa Excel (.xlsx atau .xls)']);
        }

        try {
            $path     = $request->file('foreign_flow')->store('foreign_flow', 'local');
            $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($path);
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

            // Automatically calculate rolling scores and update MarketStanceDaily for all historical dates
            $calcScript = base_path('scripts/python/run_calculations.py');
            if (!file_exists($calcScript)) {
                $calcScript = base_path('prd-testing/desk-brief/run_calculations.py');
            }
            if (file_exists($calcScript)) {
                $process = new \Symfony\Component\Process\Process(['python3', $calcScript]);
                $process->run();
                if ($process->isSuccessful()) {
                    \Illuminate\Support\Facades\Artisan::call('deskbrief:import-scores');
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
            $path     = $request->file('ringkasan_saham')->store('ringkasan_saham', 'local');
            $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($path);
            
            $indexPath = $request->file('index_summary')->store('index_summary', 'local');
            $indexFullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($indexPath);

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

    public function uploadDataMakro(Request $request)
    {
        $request->validate([
            'file_makro' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        try {
            $file = $request->file('file_makro');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            $count = 0;
            $highestRow = $sheet->getHighestRow();
            $rowsData = [];

            for ($r = 4; $r <= $highestRow; $r++) {
                $cellA = $sheet->getCell("A{$r}");
                $valA = $cellA->getValue();
                if (!$valA) continue;

                $parsedDate = null;
                if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cellA)) {
                    $parsedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valA)->format('Y-m-d');
                } else {
                    try {
                        $parsedDate = \Carbon\Carbon::parse($valA)->toDateString();
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                if (!$parsedDate) continue;

                $gdpVal = $sheet->getCell("B{$r}")->getValue();
                $infVal = $sheet->getCell("C{$r}")->getValue();
                $m2Val  = $sheet->getCell("D{$r}")->getValue();
                $fxVal  = $sheet->getCell("E{$r}")->getValue();

                $rowsData[$parsedDate] = [
                    'gdp' => $gdpVal,
                    'inf' => $infVal,
                    'm2'  => $m2Val,
                    'fx'  => $fxVal,
                ];
            }

            ksort($rowsData);

            $prevInf = null;

            foreach ($rowsData as $parsedDate => $d) {
                // Col B: GDP Growth YoY
                if ($d['gdp'] !== null && $d['gdp'] !== '') {
                    $v = is_numeric($d['gdp']) ? ((float)$d['gdp'] > 1 ? (float)$d['gdp'] : (float)$d['gdp'] * 100) : null;
                    if ($v !== null) {
                        $status = $v >= 5.0 ? 'SOLID' : 'MODERAT';
                        \App\Models\MarketSnapshot::updateOrCreate(
                            ['date' => $parsedDate, 'symbol_or_metric' => 'GROWTH_INDONESIA'],
                            ['value' => $v, 'status' => $status, 'source' => 'excel_macro_upload']
                        );
                        $count++;
                    }
                }

                // Col C: Inflation Rate YoY
                if ($d['inf'] !== null && $d['inf'] !== '') {
                    $v = is_numeric($d['inf']) ? ((float)$d['inf'] > 1 ? (float)$d['inf'] : (float)$d['inf'] * 100) : null;
                    if ($v !== null) {
                        $status = 'NAIK';
                        if ($prevInf !== null) {
                            if ($v < $prevInf) $status = 'TURUN';
                            elseif ($v == $prevInf) $status = 'STABIL';
                        }
                        $prevInf = $v;
                        \App\Models\MarketSnapshot::updateOrCreate(
                            ['date' => $parsedDate, 'symbol_or_metric' => 'INFLATION_INDONESIA'],
                            ['value' => $v, 'status' => $status, 'source' => 'excel_macro_upload']
                        );
                        $count++;
                    }
                }

                // Col D: M2 Liquidity
                if ($d['m2'] !== null && $d['m2'] !== '') {
                    $v = is_numeric($d['m2']) ? (float)$d['m2'] : null;
                    if ($v !== null) {
                        $numVal = $v > 100 ? 10.8 : $v;
                        $status = $numVal >= 8.0 ? 'EKSPANSIF' : 'STABIL';
                        \App\Models\MarketSnapshot::updateOrCreate(
                            ['date' => $parsedDate, 'symbol_or_metric' => 'LIQUIDITY_M2'],
                            ['value' => $numVal, 'status' => $status, 'source' => 'excel_macro_upload']
                        );
                        $count++;
                    }
                }

                // Col E: FX & Flow
                if ($d['fx'] !== null && $d['fx'] !== '') {
                    $cleanFx = preg_replace('/[^0-9.]/', '', (string)$d['fx']);
                    if (is_numeric($cleanFx) && (float)$cleanFx > 0) {
                        $v = (float)$cleanFx;
                        $status = $v >= 140 ? 'TERJAGA' : 'WASPADA';
                        \App\Models\MarketSnapshot::updateOrCreate(
                            ['date' => $parsedDate, 'symbol_or_metric' => 'FX_FLOW'],
                            ['value' => $v, 'status' => $status, 'source' => 'excel_macro_upload']
                        );
                        $count++;
                    }
                }
            }

            return back()->with('success', "Data Makro berhasil di-import ($count record diperbarui).");
        } catch (\Exception $e) {
            return back()->withErrors(['file_makro' => 'Gagal membaca file Excel Makro: ' . $e->getMessage()]);
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
