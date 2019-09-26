<?php

namespace Grananda\AwsFaceMatch\Tests;

use Faker\Generator;
use Mockery;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;
use Grananda\AwsFaceMatch\FaceMatchServiceProvider;

/**
 * Class TestCase.
 *
 * @package Grananda\AwsFaceMatch\Tests
 */
abstract class TestCase extends Orchestra
{
    /**
     * The Faker Generator instance.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->faker = app(Generator::class);

        $this->artisan('migrate', ['--database' => 'testbench']);

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback');
        });
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // set up database configuration
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Get AwsFaceMatch package providers.
     *
     * @param  Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            FaceMatchServiceProvider::class,
            TestServiceProvider::class,
        ];
    }
}
