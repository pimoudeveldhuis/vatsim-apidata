<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'snapshot_id',

        'connected_clients',
        'unique_users',
    ];

    protected $casts = [
        'connected_clients' => 'integer',
        'unique_users' => 'integer',
    ];
}
