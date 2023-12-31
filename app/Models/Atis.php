<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Atis extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'snapshot_id',
        'server_id',

        'atis_code',
        'atis_text',

        'cid',
        'name',
        'callsign',
        'frequency',
        'facility',
        'rating',
        'visual_range',

        'login_time',
        'last_update',
    ];

    protected $casts = [
        'atis_text' => 'array',

        'facility' => 'integer',
        'rating' => 'integer',
        'visual_range' => 'integer',

        'login_time' => 'datetime',
        'last_update' => 'datetime',
    ];
}
