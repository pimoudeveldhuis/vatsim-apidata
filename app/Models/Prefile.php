<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prefile extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'snapshot_id',

        'cid',
        'name',
        'callsign',

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

        'last_update',
    ];

    protected $casts = [
        'cid' => 'integer',
        'revision_id' => 'integer',

        'last_update' => 'datetime',
    ];
}
