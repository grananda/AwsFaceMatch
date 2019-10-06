<?php

namespace Grananda\AwsFaceMatch\Tests\Unit;

use Illuminate\Support\Facades\Bus;
use Grananda\AwsFaceMatch\Tests\TestCase;
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

        /** @var Entity $obj */
        $obj = Entity::make([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../assets/image1a.jpg',
        ]);

        // When
        $obj->save();

        // Then
        Bus::assertDispatched(StoreEntityFaceImage::class, 1);
    }

    /** @test */
    public function job_is_not_dispatched_on_model_saved_without_image_change()
    {
        // Given
        Bus::fake(StoreEntityFaceImage::class);

        /** @var Entity $obj */
        $obj = Entity::make([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../assets/image1a.jpg',
        ]);
        $obj->save();

        // When
        $obj->save();

        // Then
        Bus::assertDispatched(StoreEntityFaceImage::class, 1);
    }

    /** @test */
    public function a_proper_collection_name_is_returned_when_not_defined()
    {
        // Given
        Bus::fake(StoreEntityFaceImage::class);

        /** @var Entity $obj */
        $obj = new Entity();

        // When
        $response = $obj->getCollection();

        // Then
        $this->assertEquals(str_replace('\\', '-', get_class($obj)), $response);
    }
}
