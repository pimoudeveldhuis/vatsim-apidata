<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pilot extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_seen_snapshot_id',

        'cid',
        'name',
    ];

    protected $casts = [
        'cid' => 'integer',
    ];

    public function names()
    {
        return $this->hasMany('App\Models\Pilot\Name');
    }

    public function updateName($name, $snapshot_id) {
        $this->fill([
            'name' => $name,
        ]);

        $this->names()->create([
            'snapshot_id' => $snapshot_id,
            'name' => $name,
        ]);
    }
}
