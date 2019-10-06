<?php

namespace Grananda\AwsFaceMatch\Tests\Unit\Traits;

use Mockery;
use Aws\Result;
use Illuminate\Support\Facades\Bus;
use Aws\Rekognition\RekognitionClient;
use Grananda\AwsFaceMatch\Tests\TestCase;
use Grananda\AwsFaceMatch\Tests\Models\Entity;
use Grananda\AwsFaceMatch\Jobs\StoreEntityFaceImage;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchFaceService;
use Grananda\AwsFaceMatch\Services\AwsRekognitionClientFactory;

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
    public function entity_id_is_returned_when_requesting_a_match()
    {
        // Given
        Bus::fake(
            [
                StoreEntityFaceImage::class,
            ]
        );

        /** @var Result $resultMatch */
        $resultMatch = new Result($this->loadResponse('face_match_success'));

        /** @var Entity $model */
        $model = Entity::create([
            'uuid'      => $resultMatch->get('FaceMatches')['0']['Face']['ExternalImageId'],
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../../assets/image1a.jpg',
        ]);

        /** @var string $file */
        $file = $model->getMediaFileValue();

        /** @var string $collectionName */
        $collectionName = $model->getCollection();

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use (
                $model,
                $file,
                $collectionName,
                $resultMatch
            ) {
                $mock->shouldReceive('searchFacesByImage')
                    ->with(
                        [
                            'CollectionId'       => $collectionName,
                            'FaceMatchThreshold' => AwsFaceMatchFaceService::FACE_MATCH_THRESHOLD,
                            'Image'              => [
                                'Bytes' => file_get_contents($file),
                            ],
                            'MaxFaces' => AwsFaceMatchFaceService::MAX_RETURNED_MATCHING_FACES,
                        ]
                    )
                    ->andReturn($resultMatch)
                    ->times(1)
                ;
            });

        $this->mock('alias:'.AwsRekognitionClientFactory::class, function ($mock) use ($rekognitionClientMock) {
            $mock->shouldReceive('instantiate')
                ->andReturn($rekognitionClientMock)
            ;
        });

        // When
        $response = Entity::faceMatch($file);

        // Then
        Bus::assertDispatched(StoreEntityFaceImage::class, 1);

        $this->assertEquals($model->uuid, $response->uuid);
    }
}
