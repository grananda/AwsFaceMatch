<?php

namespace Grananda\AwsFaceMatch\Tests\Unit\Commands;

use Illuminate\Support\Facades\Bus;
use Grananda\AwsFaceMatch\Tests\TestCase;
use Grananda\AwsFaceMatch\Tests\Models\Entity;
use Grananda\AwsFaceMatch\Tests\Models\BinEntity;
use Grananda\AwsFaceMatch\Tests\Models\OtherEntity;
use Grananda\AwsFaceMatch\Jobs\StoreEntityFaceImage;
use Grananda\AwsFaceMatch\Commands\FaceMatchModelIndex;

/**
 * Class FaceMatchModelIndexTest.
 *
 * @group unit
 * @covers \Grananda\AwsFaceMatch\Commands\FaceMatchModelIndex
 *
 * @package Grananda\AwsFaceMatch\Tests\Unit\Commands
 */
class FaceMatchModelIndexTest extends TestCase
{
    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function all_records_from_different_models_are_indexed()
    {
        // Given
        Bus::fake(StoreEntityFaceImage::class);

        Entity::create([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../../assets/image1a.jpg',
        ]);

        Entity::create([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../../assets/image1a.jpg',
        ]);

        OtherEntity::create([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../../assets/image1a.jpg',
        ]);

        BinEntity::create([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => file_get_contents(__DIR__.'/../../assets/image1a.jpg'),
        ]);

        /** @var FaceMatchModelIndex $command */
        $command = resolve(FaceMatchModelIndex::class);

        // When
        $command->handle();

        // Then
        Bus::assertDispatched(StoreEntityFaceImage::class, 8);
    }
}
