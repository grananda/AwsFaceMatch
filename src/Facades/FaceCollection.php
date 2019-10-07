<?php

namespace Grananda\AwsFaceMatch\Facades;

use Illuminate\Support\Facades\Facade;

class FaceCollection extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'FaceCollection';
    }
}
