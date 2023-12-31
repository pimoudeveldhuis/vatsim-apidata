<?php

namespace App\Models\Session;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $primaryKey = null;
    protected $table = 'session_logs';
    public $incrementing = false;
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'session_id',
        'snapshot_id',

        'long',
        'lat',

        'altitude',
        'groundspeed',

        'heading',
        'qnh',
    ];

    public function snapshot()
    {
        return $this->belongsTo('App\Models\Snapshot');
    }

    public function getDatetimeAttribute()
    {
        return $this->snapshot->datetime;
    }
}
