<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ArchiveSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archive:sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive old sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Dispatch the archiving job
        \App\Jobs\ArchiveSessions::dispatch();
    }
}
