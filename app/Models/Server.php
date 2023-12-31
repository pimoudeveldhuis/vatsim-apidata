<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_seen_snapshot_id',

        'ident',
        'hostname_or_ip',
        'location',
        'name',

        'clients_connection_allowed',
        'client_connections_allowed',
        'is_sweatbox',
    ];

    protected $casts = [
        'clients_connection_allowed' => 'integer',
        'client_connections_allowed' => 'boolean',
        'is_sweatbox' => 'boolean',
    ];
}
