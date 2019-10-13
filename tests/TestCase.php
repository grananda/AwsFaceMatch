<?php

namespace Grananda\AwsFaceMatch\Tests;

use Faker\Generator;
use Illuminate\Foundation\Application;
use Grananda\AwsFaceMatch\Facades\FaceMatch;
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
     * @param \Illuminate\Foundation\Application $app
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

        $app['config']->set('facematch.aws.region', 'eu-central-1');
        $app['config']->set('facematch.aws.version', 'latest');

        $app['config']->set('facematch.recognize.Entity.collection', 'entity');
        $app['config']->set('facematch.recognize.Entity.identifier', 'uuid');
        $app['config']->set('facematch.recognize.Entity.media_file', 'media_url');

        $app['config']->set('facematch.recognize.OtherEntity.identifier', 'uuid');
        $app['config']->set('facematch.recognize.OtherEntity.media_file', 'media_url');
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

    protected function getPackageAliases($app)
    {
        return [
            'FaceMatch' => FaceMatch::class,
        ];
    }

    /**
     * Reads the contents of the given test response.
     *
     * @param string $name
     * @param bool   $json
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function loadResponse(string $name, $json = true)
    {
        $file = $json ? __DIR__."/responses/{$name}.json" : __DIR__."/responses/{$name}";

        if (file_exists($file)) {
            $content = file_get_contents($file);

            return $json ? json_decode($content, true) : $content;
        }

        throw new \Exception('Fixture was not found!');
    }
}
