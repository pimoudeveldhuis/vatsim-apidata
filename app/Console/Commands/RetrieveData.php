<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RetrieveData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retrieve:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve VATSIM data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Dispatch the retrieve data job
        \App\Jobs\RetrieveData::dispatch();
    }
}
