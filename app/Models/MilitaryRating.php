<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MilitaryRating extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',

        'short_name',
        'long_name',
    ];

    protected $casts = [
        'id' => 'integer',
    ];
}
