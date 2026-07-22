<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportDeskBriefScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deskbrief:import-scores {--momentum=avenir_foreign_flow_momentum_v2_output.csv} {--stress=avenir_market_stress_engine_output.csv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import foreign flow momentum and market stress engine scores from CSV output to market_stance_daily table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $momentumFile = $this->option('momentum');
        $stressFile = $this->option('stress');

        $this->info("Starting score import process...");

        // Auto-run Python calculation script to generate latest output CSVs if script exists
        $calcScript = base_path('scripts/python/run_calculations.py');
        if (!file_exists($calcScript)) {
            $calcScript = base_path('prd-testing/desk-brief/run_calculations.py');
        }
        if (file_exists($calcScript)) {
            $this->info("Executing calculation engine ({$calcScript})...");
            $process = new \Symfony\Component\Process\Process(['python3', $calcScript]);
            $process->run();
            if ($process->isSuccessful()) {
                $this->info("Calculations finished successfully.");
            } else {
                $this->warn("Calculation script note: " . $process->getErrorOutput());
            }
        }

        // 1. Process Momentum CSV
        if (file_exists($momentumFile)) {
            $this->info("Reading momentum file: {$momentumFile}");
            $this->importMomentumData($momentumFile);
        } else {
            $this->warn("Momentum file not found: {$momentumFile}. Skipping momentum import.");
        }

        // 2. Process Stress CSV
        if (file_exists($stressFile)) {
            $this->info("Reading stress file: {$stressFile}");
            $this->importStressData($stressFile);
        } else {
            $this->warn("Stress file not found: {$stressFile}. Skipping stress import.");
        }

        $this->info("Import process completed!");
    }

    private function importMomentumData(string $filePath)
    {
        $file = fopen($filePath, 'r');
        $headers = fgetcsv($file);
        
        if (!$headers) {
            $this->error("Failed to read headers from {$filePath}");
            return;
        }

        $dateIdx = array_search('date', $headers);
        $momentumIdx = array_search('flow_momentum_v2_score', $headers);
        $exhaustionIdx = array_search('flow_exhaustion_score', $headers);
        $reversalIdx = array_search('reversal_probability', $headers);

        if ($dateIdx === false || $momentumIdx === false || $exhaustionIdx === false || $reversalIdx === false) {
            $this->error("Required columns missing in momentum CSV.");
            return;
        }

        $count = 0;
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== false) {
                // Ensure row has enough columns
                if (count($row) <= max($dateIdx, $momentumIdx, $exhaustionIdx, $reversalIdx)) {
                    continue;
                }

                // Extract date part only (e.g. from "2026-07-20 00:00:00" to "2026-07-20")
                $dateString = trim($row[$dateIdx]);
                if (empty($dateString)) continue;
                $date = date('Y-m-d', strtotime($dateString));

                DB::table('market_stance_daily')
                    ->updateOrInsert(
                        ['date' => $date],
                        [
                            'score' => DB::raw('COALESCE(score, 50)'),
                            'label' => DB::raw("COALESCE(label, 'Neutral')"),
                            'flow_momentum_v2_score' => is_numeric($row[$momentumIdx]) ? $row[$momentumIdx] : null,
                            'flow_exhaustion_score' => is_numeric($row[$exhaustionIdx]) ? $row[$exhaustionIdx] : null,
                            'reversal_probability' => is_numeric($row[$reversalIdx]) ? $row[$reversalIdx] : null,
                            'updated_at' => now(),
                        ]
                    );
                $count++;
            }
            DB::commit();
            $this->info("Successfully processed {$count} rows from momentum CSV.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error processing momentum data: " . $e->getMessage());
            Log::error("ImportDeskBriefScores momentum error: " . $e->getMessage());
        }

        fclose($file);
    }

    private function importStressData(string $filePath)
    {
        $file = fopen($filePath, 'r');
        $headers = fgetcsv($file);
        
        if (!$headers) {
            $this->error("Failed to read headers from {$filePath}");
            return;
        }

        $dateIdx = array_search('date', $headers);
        $compositeIdx = array_search('market_stress_composite', $headers);
        $macroIdx = array_search('macro_stress', $headers);
        $flowInternalIdx = array_search('flow_internal_stress', $headers);

        if ($dateIdx === false || $compositeIdx === false || $macroIdx === false || $flowInternalIdx === false) {
            $this->error("Required columns missing in stress CSV.");
            return;
        }

        $count = 0;
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== false) {
                if (count($row) <= max($dateIdx, $compositeIdx, $macroIdx, $flowInternalIdx)) {
                    continue;
                }

                $dateString = trim($row[$dateIdx]);
                if (empty($dateString)) continue;
                $date = date('Y-m-d', strtotime($dateString));

                DB::table('market_stance_daily')
                    ->updateOrInsert(
                        ['date' => $date],
                        [
                            'score' => DB::raw('COALESCE(score, 50)'),
                            'label' => DB::raw("COALESCE(label, 'Neutral')"),
                            'market_stress_composite' => is_numeric($row[$compositeIdx]) ? $row[$compositeIdx] : null,
                            'macro_stress' => is_numeric($row[$macroIdx]) ? $row[$macroIdx] : null,
                            'flow_internal_stress' => is_numeric($row[$flowInternalIdx]) ? $row[$flowInternalIdx] : null,
                            'updated_at' => now(),
                        ]
                    );
                $count++;
            }
            DB::commit();
            $this->info("Successfully processed {$count} rows from stress CSV.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error processing stress data: " . $e->getMessage());
            Log::error("ImportDeskBriefScores stress error: " . $e->getMessage());
        }

        fclose($file);
    }
}
