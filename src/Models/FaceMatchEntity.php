<?php

namespace Grananda\AwsFaceMatch\Models;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;

class FaceMatchEntity extends Model
{
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }

    protected $table = 'face_match_entities';

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
        'collection_id',
        'face_id',
        'entity_ref',
        'image_id',
    ];

    /**
     * Collection where the face is stored.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }
}
