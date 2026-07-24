<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncLocalStorageToR2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:sync-to-r2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all local storage files (storage/app/public) to Cloudflare R2 S3 disk';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Scanning local storage files in storage/app/public...');

        $files = Storage::disk('public')->allFiles();
        // Exclude hidden files like .gitignore
        $files = array_values(array_filter($files, function ($file) {
            return ! str_starts_with(basename($file), '.');
        }));

        $total = count($files);

        if ($total === 0) {
            $this->info('No local files found in storage/app/public.');
            return 0;
        }

        $this->info("Found {$total} file(s). Starting upload to Cloudflare R2...");
        $bar = $this->output->createProgressBar($total);

        $success = 0;
        $failed = 0;

        foreach ($files as $file) {
            try {
                if (! Storage::disk('public')->exists($file)) {
                    $this->error(" Skipped {$file}: File does not exist");
                    $failed++;
                    continue;
                }

                $content = Storage::disk('public')->get($file);
                if ($content === null || $content === false) {
                    $this->error(" Failed {$file}: Unable to read file content");
                    $failed++;
                    continue;
                }

                $uploaded = Storage::disk('s3')->put($file, $content);
                if ($uploaded) {
                    $success++;
                } else {
                    $this->error(" Failed {$file}: Upload to S3/R2 returned false");
                    $failed++;
                }
            } catch (\Throwable $e) {
                $this->error(" Failed {$file}: " . $e->getMessage());
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Sync completed! Successfully uploaded: {$success}, Failed: {$failed}");

        return 0;
    }
}
