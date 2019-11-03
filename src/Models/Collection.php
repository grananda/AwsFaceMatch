<?php

namespace Grananda\AwsFaceMatch\Models;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
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
        'entity',
    ];

    /**
     * Faces within the collection.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function faces()
    {
        return $this->hasMany(FaceMatchEntity::class);
    }
}
