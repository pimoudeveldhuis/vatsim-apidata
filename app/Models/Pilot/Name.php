<?php

namespace App\Models\Pilot;

use Illuminate\Database\Eloquent\Model;

class Name extends Model
{
    public $timestamps = false;
    protected $table = 'pilot_names';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pilot_id',
        'snapshot_id',

        'name',
    ];
}
