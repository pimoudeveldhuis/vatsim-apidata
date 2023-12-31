<?php

namespace App\Jobs;

use App\Models\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class ArchiveSessions
 *
 * The archive sessions job will take all sessions that don't have updates for the past hour (so we assume they
 *     are offline and the flight has ended) and stores them into an archive table combining all information from
 *     the different tables into one row for archiving purposes and keeping the live tables as empty as possible.
 */
class ArchiveSessions implements ShouldQueue
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

        // Get all sessions that are older then the cutoff time
        $sessions = Session::where('last_update', '<', $cutoff_time)->get();
        if($sessions) {
            foreach($sessions AS $session) {
                // Only continue if there are more then two registered datapoints, else someone was online for less
                //     then a minute and probably didn't fly
                if($session->logs()->count() > 2) {
                    // Get all transponder codes used during the flight and put them into an array
                    $transponders = [];
                    foreach ($session->transponders as $transponder) {
                        $transponders[] = [
                            'dt' => $transponder->snapshot->datetime,
                            'sqw' => $transponder->transponder,
                        ];
                    }

                    // Get all flightplans filed during the flight and put them into an array
                    $flightplans = [];
                    foreach ($session->flightplans as $flightplan) {
                        $flightplans[] = [
                            'dt' => $flightplan->snapshot->datetime,
                            'rev' => $flightplan->revision_id,

                            'fr' => $flightplan->flight_rules,
                            'ac' => $flightplan->aircraft,
                            'acf' => $flightplan->aircraft_faa,
                            'acs' => $flightplan->aircraft_short,
                            'dep' => $flightplan->departure,
                            'arr' => $flightplan->arrival,
                            'altr' => $flightplan->alternate,
                            'tas' => $flightplan->cruise_tas,
                            'alt' => $flightplan->altitude,
                            'dept' => $flightplan->deptime,
                            'enr' => $flightplan->enroute_time,
                            'fuelt' => $flightplan->fuel_time,
                            'rmk' => $flightplan->remarks,
                            'rte' => $flightplan->route,
                            'at' => $flightplan->assigned_transponder,
                        ];
                    }

                    // Get all data logs (latitude, longitude, groundspeed etc.) and put them into an array
                    $log_cache = null;
                    $logs = [];
                    foreach ($session->logs as $log) {
                        // Check if the current information aligns with the log cache, because in that case we
                        //     can save the space and just not store it as the information is useless
                        if ($log_cache != $log->long . $log->lat . $log->groundspeed) {
                            $logs[] = [
                                'dt' => $log->snapshot->datetime,

                                'long' => $log->long,
                                'lat' => $log->lat,

                                'alt' => $log->altitude,
                                'gs' => $log->groundspeed,

                                'hdg' => $log->heading,
                                'qnh' => $log->qnh,
                            ];

                            // Update the log cache with the new lat, lng and groundspeed information, because if
                            //     these are the same the aircraft did not move
                            $log_cache = $log->long . $log->lat . $log->groundspeed;
                        }
                    }

                    // Create the session archive with all information gathered from the flight
                    \App\Models\Archive\Session::create([
                        'start' => $session->first_log->datetime,
                        'end' => $session->last_log->datetime,

                        'pilot_id' => $session->pilot_id,
                        'server_id' => $session->server_id,
                        'airline_id' => $session->airline_id,

                        'callsign' => $session->callsign,

                        'transponder_codes' => $transponders,
                        'flightplans' => $flightplans,

                        'logs' => $logs,

                        'finished' => ($session->has_moved && $session->has_landed),
                    ]);
                }

                // Delete the session from the live table together with all linked information
                $session->trash();
            }
        }
    }
}
