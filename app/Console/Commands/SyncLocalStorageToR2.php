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
        $this->info('Scanning local storage files...');

        $files = Storage::disk('local')->allFiles('public');
        $total = count($files);

        if ($total === 0) {
            $this->info('No local files found to sync.');
            return 0;
        }

        $this->info("Found {$total} files. Starting upload to Cloudflare R2...");
        $bar = $this->output->createProgressBar($total);

        $success = 0;
        $failed = 0;

        foreach ($files as $file) {
            $relativePath = preg_replace('/^public\//', '', $file);

            try {
                $content = Storage::disk('local')->get($file);
                Storage::disk('s3')->put($relativePath, $content);
                $success++;
            } catch (\Throwable $e) {
                $this->error(" Failed {$relativePath}: " . $e->getMessage());
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
