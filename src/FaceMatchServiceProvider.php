<?php

namespace Grananda\AwsFaceMatch;

use Illuminate\Support\ServiceProvider;

class FaceMatchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/facematch.php.php' => config_path('facematch.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
