<?php

namespace Grananda\AwsFaceMatch;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchFaceService;
use Illuminate\Foundation\Application as LaravelApplication;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchCollectionService;

class FaceMatchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setUpConfig();

        $this->loadMigrationsFrom(__DIR__.'/Migrations');
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

        App::bind('FaceCollection', function () {
            return new AwsFaceMatchCollectionService();
        });
    }

    protected function setUpConfig()
    {
        $source = dirname(__DIR__).'/resources/config/facematch.php';

        if ($this->app instanceof LaravelApplication) {
            $this->publishes([$source => config_path('facematch.php')], 'config');
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('facematch');
        }

        $this->mergeConfigFrom($source, 'facematch');
    }
}
