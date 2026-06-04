<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportAvenirLegacyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'avenir:import-legacy-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import CSV data extracted from legacy Supabase database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $basePath = base_path('public/csv-supabase');

        if (!File::isDirectory($basePath)) {
            $this->error("Directory not found: {$basePath}");
            $this->info("Please create the directory and copy the CSV files there.");
            return;
        }

        $filesToImport = [
            'profiles_rows.csv' => 'legacy_profiles',
            'research_meta_rows.csv' => 'research_metas',
            'research_likes_rows.csv' => 'research_likes',
            'comments_rows.csv' => 'comments',
            'notifications_rows.csv' => 'notifications',
            'avenir_team_emails_rows.csv' => 'avenir_team_emails',
            'payment_submissions_rows.csv' => 'payment_submissions',
            'comment_likes_rows.csv' => 'comment_likes',
            'research_views_rows.csv' => 'research_views',
            'trial_email_history_rows.csv' => 'trial_email_history',
            'pool_config_rows.csv' => 'pool_config',
            // 'unlocked_research_rows.csv' => 'unlocked_research', // Skiped as requested if not present
        ];

        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        try {
            DB::transaction(function () use ($basePath, $filesToImport) {
                foreach ($filesToImport as $file => $table) {
                    $filePath = $basePath . '/' . $file;
                    
                    if (!File::exists($filePath)) {
                        $this->warn("File not found, skipping: {$file}");
                        continue;
                    }

                    $this->info("Importing {$file} into table {$table}...");
                    
                    $data = $this->parseCsv($filePath);

                    if (empty($data)) {
                        $this->warn("No data found in {$file}");
                        continue;
                    }

                    // Process special cases
                    $data = array_map(function ($row) use ($table) {
                        // Clean all datetime and boolean fields in the row
                        foreach ($row as $key => $val) {
                            $val = $this->cleanBoolean($val);
                            $row[$key] = $this->cleanDatetime($val);
                        }

                        // Map research_id to research_meta_id for comments, research_likes, and research_views
                        if (in_array($table, ['comments', 'research_likes', 'research_views']) && isset($row['research_id'])) {
                            $row['research_meta_id'] = $row['research_id'];
                            unset($row['research_id']);
                        }

                        if ($table === 'legacy_profiles' && isset($row['spesialisasi'])) {
                            // Supabase Array might export as '{}' or '["item"]' or comma separated, depending on how export handles it.
                            // We will just keep it as a string if it's already a string, or format it to JSON if needed.
                            // Assuming CSV string is valid JSON or plain string.
                            if (!is_null($row['spesialisasi']) && !str_starts_with($row['spesialisasi'], '[')) {
                                // Basic formatting if it's `{val1,val2}` Postgres array format
                                $clean = trim($row['spesialisasi'], '{}');
                                if (!empty($clean)) {
                                    $arr = explode(',', $clean);
                                    $row['spesialisasi'] = json_encode($arr);
                                } else {
                                    $row['spesialisasi'] = json_encode([]);
                                }
                            }
                        }
                        return $row;
                    }, $data);

                    // Chunk insertion to prevent memory limit issues
                    $chunks = array_chunk($data, 500);
                    
                    $bar = $this->output->createProgressBar(count($data));
                    $bar->start();

                    foreach ($chunks as $chunk) {
                        DB::table($table)->insert($chunk);
                        $bar->advance(count($chunk));
                    }

                    $bar->finish();
                    $this->newLine();
                    $this->info("Successfully imported {$file}");
                }
            });
        } finally {
            \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
        }

        $this->info('All legacy CSV data imported successfully!');
    }

    /**
     * Clean and format boolean strings for MySQL.
     */
    private function cleanBoolean($value)
    {
        if ($value === 'true' || $value === 'TRUE') {
            return 1;
        }
        if ($value === 'false' || $value === 'FALSE') {
            return 0;
        }
        return $value;
    }

    /**
     * Clean and format datetime strings for MySQL.
     */
    private function cleanDatetime($value)
    {
        if (is_null($value)) {
            return null;
        }
        // Check if it matches ISO 8601 or Postgres timestamptz format
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}/', $value)) {
            try {
                return \Illuminate\Support\Carbon::parse($value)->toDateTimeString();
            } catch (\Exception $e) {
                return $value;
            }
        }
        return $value;
    }

    private function parseCsv($filePath)
    {
        $rows = [];
        if (($handle = fopen($filePath, "r")) !== false) {
            $headers = fgetcsv($handle, 10000, ",");
            
            // Clean BOM if exists
            if (isset($headers[0]) && str_starts_with($headers[0], "\xEF\xBB\xBF")) {
                $headers[0] = substr($headers[0], 3);
            }

            while (($data = fgetcsv($handle, 10000, ",")) !== false) {
                if (count($headers) !== count($data)) {
                    // Skip malformed rows
                    continue;
                }
                
                $row = array_combine($headers, $data);
                
                // Map empty strings to null
                $row = array_map(function ($value) {
                    return $value === '' ? null : $value;
                }, $row);

                $rows[] = $row;
            }
            fclose($handle);
        }
        return $rows;
    }
}
