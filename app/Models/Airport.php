<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Airport extends Model
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
        'city',
        'country',

        'long',
        'lat',

        'altitude',
        'timezone',
    ];

    protected $casts = [
        'altitude' => 'integer',
    ];
}
