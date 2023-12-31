<?php

namespace App\Jobs;

use App\Models\Atis;
use App\Models\Controller;
use App\Models\Prefile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class ArchiveVarious
 *
 * The archive various job will archive items other then the actual flights, like ATC, prefiled flightplans etc.
 */
class ArchiveVarious implements ShouldQueue
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
        // Cutoff time set to NOW minus an hour
        $cutoff_time = new \Carbon\Carbon(); $cutoff_time->subHour();

        // Get all ATIS information that is older then the cutoff time
        $atises = Atis::where('last_update', '<', $cutoff_time)->get();
        foreach($atises AS $atis) {
            \App\Models\Archive\Atis::create([
                'server_id' => $atis->server_id,

                'atis_code' => $atis->atis_code,
                'atis_text' => $atis->text_atis,

                'cid' => $atis->cid,
                'name' => $atis->name,
                'callsign' => $atis->callsign,
                'frequency' => $atis->frequency,
                'facility' => $atis->facility,
                'rating' => $atis->rating,
                'visual_range' => $atis->visual_range,

                'login_time' => $atis->login_time,
                'last_update' => $atis->last_update,
            ]);

            $atis->delete();
        }

        // Get all controllers that ain't online anymore because the cutoff time passed
        $controllers = Controller::where('last_update', '<', $cutoff_time)->get();
        foreach($controllers AS $controller) {
            \App\Models\Archive\Controller::create([
                'server_id' => $controller->server_id,

                'cid' => $controller->cid,
                'name' => $controller->name,
                'callsign' => $controller->callsign,
                'frequency' => $controller->frequency,
                'facility' => $controller->facility,
                'rating' => $controller->rating,
                'visual_range' => $controller->visual_range,
                'text_atis' => $controller->text_atis,
                'login_time' => $controller->login_time,
                'last_update' => $controller->last_update,
            ]);

            $controller->delete();
        }

        // Get all prefiled flightplans that haven't had an update longer then the cutoff time
        $prefiles = Prefile::where('last_update', '<', $cutoff_time)->get();
        foreach($prefiles AS $prefile) {
            \App\Models\Archive\Prefile::create([
                'cid' => $prefile->cid,
                'name' => $prefile->name,
                'callsign' => $prefile->callsign,

                'revision_id' => $prefile->revision_id,

                'flight_rules' => $prefile->flight_rules,
                'aircraft' => $prefile->aircraft,
                'aircraft_faa' => $prefile->aircraft_faa,
                'aircraft_short' => $prefile->aircraft_short,
                'departure' => $prefile->departure,
                'arrival' => $prefile->arrival,
                'alternate' => $prefile->alternate,
                'cruise_tas' => $prefile->cruise_tas,
                'altitude' => $prefile->altitude,
                'deptime' => $prefile->deptime,
                'enroute_time' => $prefile->enroute_time,
                'fuel_time' => $prefile->fuel_time,
                'remarks' => $prefile->remarks,
                'route' => $prefile->route,
                'assigned_transponder' => $prefile->assigned_transponder,

                'last_update' => $prefile->last_update,
            ]);

            $prefile->delete();
        }
    }
}
