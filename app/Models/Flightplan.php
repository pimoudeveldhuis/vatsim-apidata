<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flightplan extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'session_id',
        'snapshot_id',

        'revision_id',

        'flight_rules',
        'aircraft',
        'aircraft_faa',
        'aircraft_short',
        'departure',
        'arrival',
        'alternate',
        'cruise_tas',
        'altitude',
        'deptime',
        'enroute_time',
        'fuel_time',
        'remarks',
        'route',
        'assigned_transponder',
    ];

    protected $casts = [
        'revision_id' => 'integer',
    ];

    public function snapshot()
    {
        return $this->belongsTo('App\Models\Snapshot');
    }
}
