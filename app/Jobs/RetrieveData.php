<?php

namespace App\Jobs;

use App\Models\Airline;
use App\Models\Atis;
use App\Models\Client;
use App\Models\Controller;
use App\Models\Facility;
use App\Models\Flightplan;
use App\Models\Pilot;
use App\Models\Prefile;
use App\Models\Server;
use App\Models\Session;
use App\Models\Snapshot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Class RetrieveData
 *
 * The retrieve data job is the center of the whole project, where everything comes together. This job will get all
 *     ATIS information available, all controllers that are currently active, all prefiled flightplans and all current
 *     flights and the information on the pilot flying.
 */
class RetrieveData implements ShouldQueue
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
        // Get the actual server that needs to be queried for the information from our config file in storage
        $vatsim_servers = explode(';', base64_decode(Storage::get('configs/servers/data.dm')));
        $vatsim_server = $vatsim_servers[rand(0, count($vatsim_servers) - 1)];

        try {
            // Try to retrieve the data set
            $data = file_get_contents($vatsim_server);
        } catch(\Exception $e) {
            $data = null;
        }

        // Decode all available data
        $data = json_decode($data);

        try {
            // Try to create a snapshot based on the last update timestamp from the VATSIM API. It might occur that
            //     due to some delay on the VATSIM API we got the same data twice. In that case we ditch this update
            //     and check again in 15 seconds
            $snapshot_timestamp = \Carbon\Carbon::parse($data->general->update_timestamp);
            if(Snapshot::where('datetime', $snapshot_timestamp->format('d-m-Y H:i:s'))->count() == 0) {
                $snapshot = Snapshot::create([
                    'datetime' => $data->general->update_timestamp,
                ]);
            }
        } catch(\Exception $e) {
            $snapshot = null;
        }

        if($snapshot) {
            // Start with storing the VATSIM client data, i.e.: the number of online users statistics
            Client::create([
                'snapshot_id' => $snapshot->id,
                'connected_clients' => $data->general->connected_clients,
                'unique_users' => $data->general->unique_users,
            ]);

            // Retrieve all VATSIM servers and if not existing in the database yet store it there
            foreach($data->servers AS $server) {
                if(Server::where('ident', $server->ident)->count() === 0) {
                    Server::create([
                        'first_seen_snapshot_id' => $snapshot->id,
                        'ident' => $server->ident,
                        'hostname_or_ip' => $server->hostname_or_ip,
                        'location' => $server->location,
                        'name' => $server->name,
                        'clients_connection_allowed' => $server->clients_connection_allowed,
                        'client_connections_allowed' => $server->client_connections_allowed,
                        'is_sweatbox' => $server->is_sweatbox,
                    ]);
                }
            }

            // Retrieve all pilot information
            foreach($data->pilots AS $pdata) {
                // If the pilot is already known in our system, check whether the name has to be updated or if the
                //     pilot is seen for the first time, insert that information
                $pilot = Pilot::where('cid', $pdata->cid)->first();
                if($pilot) {
                    if($pilot->name !== $pdata->name) {
                        $pilot->updateName($pdata->name, $snapshot->id);
                    }
                } else {
                    $pilot = Pilot::create([
                        'first_seen_snapshot_id' => $snapshot->id,

                        'cid' => $pdata->cid,
                        'name' => $pdata->name,
                    ]);

                    $pilot->names()->create([
                        'snapshot_id' => $snapshot->id,
                        'name' => $pdata->name,
                    ]);
                }

                // With the logon time, try to get the existing session
                $logon_time = \Carbon\Carbon::parse($pdata->logon_time);
                $session = Session::where('pilot_id', $pilot->id)->where('login_time', $logon_time->format('Y-m-d H:i:s'))->first();
                if($session && $session->callsign === $pdata->callsign) {
                    // If the session exists, and the callsign is identical, we have the same flight still going on
                    if($session->transponder !== $pdata->transponder) {
                        // Update the transponder if it is changed
                        $session->updateTransponder($pdata->transponder, $snapshot->id);
                    }

                    // If the flight currently does not have a flightplan, or the revision id of the active flightplan
                    //     changed then create a new flightplan with the new information
                    if(isset($pdata->flight_plan) &&
                        ($session->flightplan === null || $pdata->flight_plan->revision_id != $session->flightplan_revision_id)
                    ) {
                        Flightplan::create([
                            'session_id' => $session->id,
                            'snapshot_id' => $snapshot->id,

                            'revision_id' => $pdata->flight_plan->revision_id,

                            'flight_rules' => $pdata->flight_plan->flight_rules,
                            'aircraft' => $pdata->flight_plan->aircraft,
                            'aircraft_faa' => $pdata->flight_plan->aircraft_faa,
                            'aircraft_short' => $pdata->flight_plan->aircraft_short,
                            'departure' => $pdata->flight_plan->departure,
                            'arrival' => $pdata->flight_plan->arrival,
                            'alternate' => $pdata->flight_plan->alternate,
                            'cruise_tas' => $pdata->flight_plan->cruise_tas,
                            'altitude' => $pdata->flight_plan->altitude,
                            'deptime' => $pdata->flight_plan->deptime,
                            'enroute_time' => $pdata->flight_plan->enroute_time,
                            'fuel_time' => $pdata->flight_plan->fuel_time,
                            'remarks' => $pdata->flight_plan->remarks,
                            'route' => $pdata->flight_plan->route,
                            'assigned_transponder' => $pdata->flight_plan->assigned_transponder,
                        ]);

                        $session->fill([
                            'flightplan_revision_id' => $pdata->flight_plan->revision_id,
                        ])->save();
                    }

                    $session->fill([
                        'last_update' => $pdata->last_updated,
                    ])->save();
                } else {
                    // The session doesn't exist or the callsign is different and we can assume that the pilot
                    //     has started a new flight, so set the session variable to null so a new flight is created
                    $session = null;
                }

                // Create a new flight (but only if the groundspeed is zero as we do not want to start logging
                //     flights midway in a flight)
                if($session === null && $pdata->groundspeed == 0) {
                    // Get the server the pilot is using and continue when it exists
                    $server = Server::where('ident', $pdata->server)->first();
                    if($server) {
                        // Try to find the airline the pilot is flying for
                        $airline = Airline::where('ICAO', substr($pdata->callsign, 0, 3))->first();

                        // Create the actual session
                        $session = Session::create([
                            'snapshot_id' => $snapshot->id,
                            'pilot_id' => $pilot->id,
                            'server_id' => $server->id,
                            'airline_id' => ($airline !== null) ? $airline->id : null,

                            'callsign' => $pdata->callsign,
                            'transponder' => $pdata->transponder,

                            'flightplan_revision_id' => (isset($pdata->flight_plan) ? $pdata->flight_plan->revision_id : null),

                            'login_time' => $pdata->logon_time,
                            'last_update' => $pdata->last_updated,
                        ]);

                        // Save the current transponder code used
                        $session->transponders()->create([
                            'snapshot_id' => $snapshot->id,
                            'transponder' => $pdata->transponder,
                        ]);

                        // If there is a flightplan filed, insert it
                        if(isset($pdata->flight_plan)) {
                            Flightplan::create([
                                'session_id' => $session->id,
                                'snapshot_id' => $snapshot->id,

                                'revision_id' => $pdata->flight_plan->revision_id,

                                'flight_rules' => $pdata->flight_plan->flight_rules,
                                'aircraft' => $pdata->flight_plan->aircraft,
                                'aircraft_faa' => $pdata->flight_plan->aircraft_faa,
                                'aircraft_short' => $pdata->flight_plan->aircraft_short,
                                'departure' => $pdata->flight_plan->departure,
                                'arrival' => $pdata->flight_plan->arrival,
                                'alternate' => $pdata->flight_plan->alternate,
                                'cruise_tas' => $pdata->flight_plan->cruise_tas,
                                'altitude' => $pdata->flight_plan->altitude,
                                'deptime' => $pdata->flight_plan->deptime,
                                'enroute_time' => $pdata->flight_plan->enroute_time,
                                'fuel_time' => $pdata->flight_plan->fuel_time,
                                'remarks' => $pdata->flight_plan->remarks,
                                'route' => $pdata->flight_plan->route,
                                'assigned_transponder' => $pdata->flight_plan->assigned_transponder,
                            ]);
                        }
                    } else {
                        // The server used does not exist, which should not happen except when for example VATSIM
                        //     developers are testing on servers that are not listed in the server list as you
                        //     cannot fly on them so we skip this for our data crawler
                    }
                }

                if($session) {
                    // Create a data log of the current transponder information, for example heading and groundspeed
                    $session->logs()->create([
                        'snapshot_id' => $snapshot->id,

                        'long' => $pdata->longitude,
                        'lat' => $pdata->latitude,

                        'altitude' => $pdata->altitude,
                        'groundspeed' => $pdata->groundspeed,

                        'heading' => $pdata->heading,
                        'qnh' => $pdata->qnh_mb,
                    ]);
                }
            }

            // Go through all online controllers
            foreach($data->controllers AS $cdata) {
                $logon_time = \Carbon\Carbon::parse($cdata->logon_time);
                $controller = Controller::where('cid', $cdata->cid)->where('login_time', $logon_time->format('Y-m-d H:i:s'))->first();
                if($controller) {
                    // If the controller was already active, we only update the last updated timestamp
                    $controller->fill([
                        'last_update' => $cdata->last_updated,
                    ])->save();
                } else {
                    $server = Server::where('ident', $cdata->server)->first();
                    if($server) {
                        // As the controller did not exist yet in the previous run we insert all information
                        Controller::create([
                            'server_id' => $server->id,
                            'snapshot_id' => $snapshot->id,

                            'cid' => $cdata->cid,
                            'name' => $cdata->name,
                            'callsign' => $cdata->callsign,
                            'frequency' => $cdata->frequency,
                            'facility' => $cdata->facility,
                            'rating' => $cdata->rating,
                            'visual_range' => $cdata->visual_range,
                            'text_atis' => $cdata->text_atis,
                            'login_time' => $cdata->logon_time,
                            'last_update' => $cdata->last_updated,
                        ]);
                    } else {
                        // The server used does not exist, which should not happen except when for example VATSIM
                        //     developers are testing on servers that are not listed in the server list as you
                        //     cannot connect to those servers, so we skip this for our data crawler
                    }
                }
            }

            // Go through all ATIS information and update or insert it into the ATIS database table
            foreach($data->atis AS $adata) {
                $logon_time = \Carbon\Carbon::parse($adata->logon_time);
                $atis = Atis::where('cid', $adata->cid)->where('atis_code', $adata->atis_code)->where('login_time', $logon_time->format('Y-m-d H:i:s'))->first();

                if($atis) {
                    $atis->fill([
                        'last_update' => $adata->last_updated,
                    ])->save();
                } else {
                    $server = Server::where('ident', $adata->server)->first();
                    if($server) {
                        Atis::create([
                            'snapshot_id' => $snapshot->id,
                            'server_id' => $server->id,

                            'atis_code' => $adata->atis_code,
                            'atis_text' => $adata->text_atis,

                            'cid' => $adata->cid,
                            'name' => $adata->name,
                            'callsign' => $adata->callsign,
                            'frequency' => $adata->frequency,
                            'facility' => $adata->facility,
                            'rating' => $adata->rating,
                            'visual_range' => $adata->visual_range,

                            'login_time' => $adata->logon_time,
                            'last_update' => $adata->last_updated,
                        ]);
                    }
                }
            }

            // Go through all prefiled flightplans
            foreach($data->prefiles AS $pfdata) {
                $last_update = \Carbon\Carbon::parse($pfdata->last_updated);

                if(isset($pfdata->flight_plan)) {
                    // If there is an actual flightplan filed with the prefile, retrieve it based on the last update information, but also include the revision of the flightplan
                    $prefile = Prefile::where('cid', $pfdata->cid)->where('revision_id', $pfdata->flight_plan->revision_id)->where('last_update', $last_update->format('Y-m-d H:i:s'))->first();
                } else {
                    // If there is no actual flightplan filed yet in the prefile then we skip the rivision id as it does not exists yet
                    $prefile = Prefile::where('cid', $pfdata->cid)->where('login_time', $last_update->format('Y-m-d H:i:s'))->first();
                }

                // If the prefile is null and a flightplan exists
                if($prefile === null && isset($pfdata->flight_plan)) {
                    Prefile::create([
                        'snapshot_id' => $snapshot->id,

                        'cid' => $pfdata->cid,
                        'name' => $pfdata->name,
                        'callsign' => $pfdata->callsign,

                        'revision_id' => $pfdata->flight_plan->revision_id,

                        'flight_rules' => $pfdata->flight_plan->flight_rules,
                        'aircraft' => $pfdata->flight_plan->aircraft,
                        'aircraft_faa' => $pfdata->flight_plan->aircraft_faa,
                        'aircraft_short' => $pfdata->flight_plan->aircraft_short,
                        'departure' => $pfdata->flight_plan->departure,
                        'arrival' => $pfdata->flight_plan->arrival,
                        'alternate' => $pfdata->flight_plan->alternate,
                        'cruise_tas' => $pfdata->flight_plan->cruise_tas,
                        'altitude' => $pfdata->flight_plan->altitude,
                        'deptime' => $pfdata->flight_plan->deptime,
                        'enroute_time' => $pfdata->flight_plan->enroute_time,
                        'fuel_time' => $pfdata->flight_plan->fuel_time,
                        'remarks' => $pfdata->flight_plan->remarks,
                        'route' => $pfdata->flight_plan->route,
                        'assigned_transponder' => $pfdata->flight_plan->assigned_transponder,

                        'last_update' => $pfdata->last_updated,
                    ]);
                }
            }

            // Go through all facilities and either insert or update them in the facilities database table
            foreach($data->facilities AS $fdata) {
                $facility = Facility::find($fdata->id);
                if($facility) {
                    $facility->fill([
                        'short' => $fdata->short,
                        'long' => $fdata->long,
                    ])->save();
                } else {
                    Facility::create([
                        'id' => $fdata->id,
                        'short' => $fdata->short,
                        'long' => $fdata->long,
                    ]);
                }
            }
        }
    }
}
