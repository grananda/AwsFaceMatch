<?php

namespace Grananda\AwsFaceMatch\Tests;

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
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'testbench']);

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback');
        });
    }

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
     * @param Application $app
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

    /**
     * Mock the event dispatcher so all events are silenced and collected.
     *
     * @return TestCase
     */
    protected function withoutEvents()
    {
        $mock = Mockery::mock(Dispatcher::class);

        $mock->shouldReceive('fire', 'until');

        $this->app->instance('events', $mock);

        return $this;
    }
}
