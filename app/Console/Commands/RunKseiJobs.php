<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

class RunKseiJobs extends Command
{
    protected $signature = 'ksei:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run KSEI extraction jobs manually';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Running Job for Snapshot 2...");
        $job2 = new \App\Jobs\ExtractKseiOwnershipJob(2, null);
        $job2->handle();

        $this->info("Running Job for Snapshot 1...");
        $job1 = new \App\Jobs\ExtractKseiOwnershipJob(1, 2);
        $job1->handle();

        $this->info("Done!");
    }
}
