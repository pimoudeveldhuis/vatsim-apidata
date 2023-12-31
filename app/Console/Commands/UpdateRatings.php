<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateRatings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:ratings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update VATSIM ratings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Dispatch the update ratings job
        \App\Jobs\UpdateRatings::dispatch();
    }
}
