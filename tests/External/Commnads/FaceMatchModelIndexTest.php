<?php

namespace Grananda\AwsFaceMatch\Tests\External\Commands;

use Illuminate\Support\Facades\Bus;
use Grananda\AwsFaceMatch\Tests\TestCase;
use Grananda\AwsFaceMatch\Tests\Models\Entity;
use Grananda\AwsFaceMatch\Tests\Models\OtherEntity;
use Grananda\AwsFaceMatch\Jobs\StoreEntityFaceImage;
use Grananda\AwsFaceMatch\Commands\FaceMatchModelIndex;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchCollectionService;

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
     */
    public function all_records_from_different_models_are_indexed_with()
    {
        Bus::fake(StoreEntityFaceImage::class);

        // Given
        /** @var Entity $model1 */
        $model1 = Entity::create([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../../assets/image1a.jpg',
        ]);

        OtherEntity::create([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../../assets/image2a.jpg',
        ]);

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        /** @var FaceMatchModelIndex $command */
        $command = resolve(FaceMatchModelIndex::class);

        // When
        $command->handle();

        $response1 = Entity::faceMatch(__DIR__.'/../../assets/image1b.jpg');
        $response2 = OtherEntity::faceMatch(__DIR__.'/../../assets/image1b.jpg');

        // Then
        Bus::assertDispatched(StoreEntityFaceImage::class, 2);

        $this->assertEquals($response1->uuid, $model1->uuid);
        $this->assertFalse($response2);

        $collectionService->purgeCollections();
    }
}
