<?php

namespace Grananda\AwsFaceMatch\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
            'name',
        ];
}
