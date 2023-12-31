<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'snapshot_id',
        'pilot_id',
        'server_id',
        'airline_id',

        'callsign',
        'transponder',

        'flightplan_revision_id',

        'login_time',
        'last_update',
    ];

    protected $casts = [
        'flightplan_revision_id' => 'integer',

        'login_time' => 'datetime',
        'last_update' => 'datetime',
    ];

    public function flightplans()
    {
        return $this->hasMany('App\Models\Flightplan');
    }

    public function getFlightplanAttribute()
    {
        return $this->flightplans()->orderBy('snapshot_id', 'DESC')->first();
    }

    public function transponders()
    {
        return $this->hasMany('App\Models\Session\Transponder');
    }

    public function logs()
    {
        return $this->hasMany('App\Models\Session\Log');
    }

    public function getFirstLogAttribute()
    {
        return $this->logs()->orderBy('snapshot_id', 'ASC')->first();
    }

    public function getLastLogAttribute()
    {
        return $this->logs()->orderBy('snapshot_id', 'DESC')->first();
    }

    /**
     * The hasMoved variable is a boolean that returns true when the plane has moved at some point
     *
     * @return bool
     */
    public function getHasMovedAttribute()
    {
        return $this->logs()->where('groundspeed', '>', 0)->count() > 0;
    }

    /**
     * The hasLanded variable is a boolean variable which returns whether an aircraft is come to a standstill.
     *     This can be because the plane is landed and parked or because it hasn't moved yet. Usually this is
     *     therefor combined with the hasMoved attribute.
     *
     * @return bool
     */
    public function getHasLandedAttribute()
    {
        return $this->logs()->orderBy('snapshot_id', 'DESC')->value('groundspeed') == 0;
    }

    public function updateTransponder($transponder, $snapshot_id) {
        $this->fill([
            'transponder' => $transponder,
        ]);

        $this->transponders()->create([
            'snapshot_id' => $snapshot_id,
            'transponder' => $transponder,
        ]);
    }

    /**
     * The trash function does delete the session from the database, but also
     *     trashes the information connected to this session, e.g. the logs,
     *     transponder and flightplan information.
     *
     * @return void
     */
    public function trash()
    {
        $this->logs()->delete();
        $this->transponders()->delete();
        $this->flightplans()->delete();

        $this->delete();
    }
}
