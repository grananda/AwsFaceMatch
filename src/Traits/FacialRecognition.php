<?php

namespace Grananda\AwsFaceMatch\Traits;

use Grananda\AwsFaceMatch\Jobs\EntityImageWasStored;

trait FacialRecognition
{
    public static function boot()
    {
        parent::boot();

        static::saved(function (self $model) {
            EntityImageWasStored::dispatch($model);
        });
    }
}
