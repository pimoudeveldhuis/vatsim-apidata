<?php

namespace App\Models\Archive;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    public $timestamps = false;
    protected $table = 'archive_sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'start',
        'end',

        'pilot_id',
        'server_id',
        'airline_id',

        'callsign',

        'transponder_codes',
        'flightplans',

        'logs',

        'finished',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',

        'transponder_codes' => 'array',
        'flightplans' => 'array',
        'logs' => 'array',

        'finished' => 'boolean',
    ];
}
