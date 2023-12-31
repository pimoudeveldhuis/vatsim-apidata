<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Class UpdateServers
 *
 * The UpdateServers job will update the servers to use from the VATSIM network. It will do this by taking the
 *     URL from the enviroment file and use that URL to query for all the server urls that should be used for the
 *     other jobs.
 */
class UpdateServers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Retrieve the data about the VATSIM API urls from the VATSIM config file that is published on the
            //     url specified in the enviroment file
            $data = file_get_contents(config('vatsim.servers'));
        } catch(\Exception $e) {
            $data = null;
        }

        if($data !== null) {
            // Decode the returned data to an object
            $data = json_decode($data);

            try {
                // Try to assign the v3 API server urls to the variable
                $data_servers = $data->data->v3;
            } catch(\Exception $e) {
                $data_servers = null;
            }

            if($data_servers !== null) {
                // If everything succeeded then implode all server URLS and put it in our own config file
                //     that is saved in storage and used by the other jobs
                $data_servers = implode(';', $data_servers);

                Storage::put('configs/servers/data.dm', base64_encode($data_servers));
            }
        }
    }
}
