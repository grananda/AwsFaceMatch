<?php

namespace Grananda\AwsFaceMatch\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Grananda\AwsFaceMatch\Traits\FacialRecognition;
use Ramsey\Uuid\Uuid;

class FaceMatchEntity extends Model
{

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'collection_arn',
        'collection_id',
        'face_id',
        'entity',
        'entity_ref',
    ];
}
