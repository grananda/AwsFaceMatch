<?php

namespace Grananda\AwsFaceMatch\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Grananda\AwsFaceMatch\Traits\FacialRecognition;

class Entity extends Model
{
    use FacialRecognition;

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
