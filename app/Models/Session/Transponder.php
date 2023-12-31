<?php

namespace App\Models\Session;

use Illuminate\Database\Eloquent\Model;

class Transponder extends Model
{
    protected $primaryKey = null;
    protected $table = 'session_transponders';
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

        'transponder',
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
