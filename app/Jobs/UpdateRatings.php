<?php

namespace App\Jobs;

use App\Models\MilitaryRating;
use App\Models\PilotRating;
use App\Models\Rating;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class UpdateRatings implements ShouldQueue
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
        // Retrieve the VATSIM server URL from our config file in storage
        $vatsim_servers = explode(';', base64_decode(Storage::get('configs/servers/data.dm')));
        $vatsim_server = $vatsim_servers[rand(0, count($vatsim_servers) - 1)];

        try {
            // Get all data content
            $data = file_get_contents($vatsim_server);
        } catch(\Exception $e) {
            $data = null;
        }

        // Decode the data to an object
        $data = json_decode($data);

        if($data) {
            // Update or create the account ratings
            foreach ($data->ratings as $rdata) {
                $rating = Rating::find($rdata->id);
                if ($rating) {
                    $rating->fill([
                        'short' => $rdata->short,
                        'long' => $rdata->long,
                    ])->save();
                } else {
                    Rating::create([
                        'id' => $rdata->id,
                        'short' => $rdata->short,
                        'long' => $rdata->long,
                    ]);
                }
            }

            // Update or create the pilot ratings
            foreach ($data->pilot_ratings as $prdata) {
                $pilot_rating = PilotRating::find($prdata->id);
                if ($pilot_rating) {
                    $pilot_rating->fill([
                        'short_name' => $prdata->short_name,
                        'long_name' => $prdata->long_name,
                    ])->save();
                } else {
                    PilotRating::create([
                        'id' => $prdata->id,
                        'short_name' => $prdata->short_name,
                        'long_name' => $prdata->long_name,
                    ]);
                }
            }

            // Update or create the military ratings
            foreach ($data->military_ratings as $mrdata) {
                $military_rating = MilitaryRating::find($mrdata->id);
                if ($military_rating) {
                    $military_rating->fill([
                        'short_name' => $mrdata->short_name,
                        'long_name' => $mrdata->long_name,
                    ])->save();
                } else {
                    MilitaryRating::create([
                        'id' => $mrdata->id,
                        'short_name' => $mrdata->short_name,
                        'long_name' => $mrdata->long_name,
                    ]);
                }
            }
        }
    }
}
