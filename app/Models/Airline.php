<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Airline extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'icao',
        'iata',

        'name',
        'callsign',
        'country',

        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
