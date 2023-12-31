<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ArchiveVarious extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archive:various';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive old information';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Dispatch the various archiving job
        \App\Jobs\ArchiveVarious::dispatch();
    }
}
