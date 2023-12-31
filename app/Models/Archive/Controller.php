<?php

namespace App\Models\Archive;

use Illuminate\Database\Eloquent\Model;

class Controller extends Model
{
    public $timestamps = false;
    protected $table = 'archive_controllers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'server_id',

        'cid',
        'name',
        'frequency',
        'facility',
        'rating',
        'visual_range',
        'text_atis',

        'login_time',
        'last_update',
    ];

    protected $casts = [
        'cid' => 'integer',
        'facility' => 'integer',
        'rating' => 'integer',
        'visual_range' => 'integer',

        'text_atis' => 'array',

        'login_time' => 'datetime',
        'last_update' => 'datetime',
    ];
}
