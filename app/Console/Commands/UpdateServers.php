<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateServers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:servers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the VATSIM servers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Dispatch the update servers jobs
        \App\Jobs\UpdateServers::dispatch();
    }
}
