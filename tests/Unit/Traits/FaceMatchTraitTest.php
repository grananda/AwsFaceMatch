<?php

namespace Grananda\AwsFaceMatch\Tests\Unit\Traits;

use Illuminate\Support\Facades\Bus;
use Grananda\AwsFaceMatch\Tests\TestCase;
use Grananda\AwsFaceMatch\Jobs\FindFaceMatch;
use Grananda\AwsFaceMatch\Tests\Models\Entity;
use Grananda\AwsFaceMatch\Jobs\StoreEntityFaceImage;

/**
 * Class FaceMatchTraitTest.
 *
 * @package Grananda\AwsFaceMatch\Test
 *
 * @group unit
 * @covers \Grananda\AwsFaceMatch\Traits\FacialRecognition
 */
class FaceMatchTraitTest extends TestCase
{
    /** @test */
    public function job_is_dispatched_on_model_saved()
    {
        // Given
        Bus::fake(StoreEntityFaceImage::class);

        /** @var Entity $model */
        $model = Entity::make([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../../assets/image1a.jpg',
        ]);

        // When
        $model->save();

        // Then
        Bus::assertDispatched(StoreEntityFaceImage::class, 1);
    }

    /** @test */
    public function job_is_not_dispatched_on_model_saved_without_image_change()
    {
        // Given
        Bus::fake(StoreEntityFaceImage::class);

        /** @var Entity $model */
        $model = Entity::make([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../../assets/image1a.jpg',
        ]);
        $model->save();

        // When
        $model->save();

        // Then
        Bus::assertDispatched(StoreEntityFaceImage::class, 1);
    }

    /** @test */
    public function a_proper_collection_name_is_returned_when_not_defined()
    {
        // Given
        Bus::fake(StoreEntityFaceImage::class);

        /** @var Entity $model */
        $model = new Entity();

        // When
        $response = $model->getCollection();

        // Then
        $this->assertEquals(str_replace('\\', '-', get_class($model)), $response);
    }

    /**
     * @test
     */
    public function face_match_job_is_triggered_when_()
    {
        // Given
        Bus::fake(
            [
                StoreEntityFaceImage::class,
                FindFaceMatch::class,
            ]
        );

        Entity::create([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../../assets/image1a.jpg',
        ]);

        /** @var string $file */
        $file = __DIR__.'/../../assets/image1b.jpg';

        // When
        Entity::faceMatch($file);

        // Then
        Bus::assertDispatched(StoreEntityFaceImage::class, 1);
        Bus::assertDispatched(FindFaceMatch::class, 1);
    }
}
