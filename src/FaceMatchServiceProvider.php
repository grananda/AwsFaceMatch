<?php

namespace Grananda\AwsFaceMatch;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchFaceService;

class FaceMatchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        App::bind('FaceMatch', function () {
            return new AwsFaceMatchFaceService();
        });
    }
}
