<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SyncVercelHtml extends Command
{
    protected $signature = 'avenir:sync-html';
    protected $description = 'Sync HTML files from testingavenir folder to storage/app/website';

    public function handle()
    {
        $sourcePath = base_path('app/website/baru');
        $destPath = storage_path('app/website');

        if (!File::isDirectory($sourcePath)) {
            $this->error("Source directory not found: {$sourcePath}");
            return;
        }

        if (!File::isDirectory($destPath)) {
            File::makeDirectory($destPath, 0755, true);
        }

        $files = File::files($sourcePath);
        $count = 0;

        foreach ($files as $file) {
            if ($file->getExtension() === 'html') {
                File::copy($file->getPathname(), $destPath . '/' . $file->getFilename());
                $this->info("Synced: " . $file->getFilename());
                $count++;
            }
        }

        $this->info("Successfully synced {$count} HTML files.");
    }
}
