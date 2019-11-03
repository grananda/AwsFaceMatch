<?php

namespace Grananda\AwsFaceMatch\Tests\Unit\Traits;

use Mockery;
use Aws\Result;
use Illuminate\Support\Facades\Bus;
use Aws\Rekognition\RekognitionClient;
use Grananda\AwsFaceMatch\Tests\TestCase;
use Grananda\AwsFaceMatch\Tests\Models\Entity;
use Grananda\AwsFaceMatch\Tests\Models\BinEntity;
use Grananda\AwsFaceMatch\Tests\Models\OtherEntity;
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
    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
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

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function job_is_dispatched_on_binary_model_saved()
    {
        // Given
        Bus::fake(StoreEntityFaceImage::class);

        /** @var Entity $model */
        $model = BinEntity::make([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => file_get_contents(__DIR__.'/../../assets/image1a.jpg'),
        ]);

        // When
        $model->save();

        // Then
        Bus::assertDispatched(StoreEntityFaceImage::class, 1);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
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

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function job_is_not_dispatched_on_model_when_image_is_missing()
    {
        // Given
        Bus::fake(StoreEntityFaceImage::class);

        /** @var Entity $model */
        $model = Entity::make([
            'uuid' => $this->faker->uuid,
            'name' => $this->faker->name,
        ]);

        // When
        $model->save();

        // Then
        Bus::assertNotDispatched(StoreEntityFaceImage::class);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function a_proper_collection_name_is_returned_when_not_defined()
    {
        // Given
        Bus::fake(StoreEntityFaceImage::class);

        /** @var Entity $model */
        $model = new OtherEntity();

        // When
        $response = $model->getCollection();

        // Then
        $this->assertEquals(str_replace('\\', '-', get_class($model)), $response);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
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
        $file = $model->getMediaFieldValue();

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

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function entity_id_is_returned_when_requesting_a_match_from_binary()
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
            'media_url' => file_get_contents(__DIR__.'/../../assets/image1a.jpg'),
        ]);

        /** @var string $file */
        $file = $model->getMediaFieldValue();

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
                                'Bytes' => $file,
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
        $response = Entity::faceMatch(__DIR__.'/../../assets/image1a.jpg');

        // Then
        Bus::assertDispatched(StoreEntityFaceImage::class, 1);

        $this->assertEquals($model->uuid, $response->uuid);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function a_collection_is_removed()
    {
        // Given
        Bus::fake(
            [
                StoreEntityFaceImage::class,
            ]
        );

        /** @var Result $resultMatch */
        $resultDelete = new Result($this->loadResponse('collection_delete_success'));

        /** @var Entity $model */
        $model = Entity::create([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../../assets/image1a.jpg',
        ]);

        /** @var string $collectionName */
        $collectionName = $model->getCollection();

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use (
                $collectionName,
                $resultDelete
            ) {
                $mock->shouldReceive('deleteCollection')
                    ->with(
                        [
                            'CollectionId' => $collectionName,
                        ]
                    )
                    ->andReturn($resultDelete)
                    ->times(1)
                ;
            });

        $this->mock('alias:'.AwsRekognitionClientFactory::class, function ($mock) use ($rekognitionClientMock) {
            $mock->shouldReceive('instantiate')
                ->andReturn($rekognitionClientMock)
            ;
        });

        // When
        /** @var Result $response */
        $response = Entity::purgeCollection();

        // Then
        Bus::assertDispatched(StoreEntityFaceImage::class, 1);

        $this->assertEquals(200, $response->get('StatusCode'));
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function a_record_is_forgotten()
    {
        // Given
        Bus::fake(
            [
                StoreEntityFaceImage::class,
            ]
        );

        /** @var Result $resultDelete */
        $resultDelete = new Result($this->loadResponse('face_delete_success'));

        /** @var string $faceId */
        $faceId = $resultDelete->get('DeletedFaces')[0];

        /** @var Entity $model */
        $model = Entity::create([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../../assets/image1a.jpg',
        ]);

        /** @var string $collectionName */
        $collectionName = $model->getCollection();

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use (
                $faceId,
                $collectionName,
                $resultDelete
            ) {
                $mock->shouldReceive('deleteFaces')
                    ->with(
                        [
                            'CollectionId' => $collectionName,
                            'FaceIds'      => [
                                $faceId,
                            ],
                        ]
                    )
                    ->andReturn($resultDelete)
                    ->times(1)
                ;
            });

        $this->mock('alias:'.AwsRekognitionClientFactory::class, function ($mock) use ($rekognitionClientMock) {
            $mock->shouldReceive('instantiate')
                ->andReturn($rekognitionClientMock)
            ;
        });

        // When
        $response = Entity::facesForget([
            $faceId,
        ]);

        // Then
        Bus::assertDispatched(StoreEntityFaceImage::class, 1);

        $this->assertTrue(in_array($faceId, $response));
    }
}
