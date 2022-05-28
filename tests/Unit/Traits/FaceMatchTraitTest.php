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
use Grananda\AwsFaceMatch\Jobs\RemoveEntityFaceImage;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchService;
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
    public function jobIsDispatchedOnModelSaved()
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
    public function jobIsDispatchedOnBinaryModelSaved()
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
    public function jobIsNotDispatchedOnModelSavedWithoutImageChange()
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
    public function jobIsNotDispatchedOnModelWhenImageIsMissing()
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
    public function jobIsDispatchedWhenRecordIsRemoved()
    {
        // Given
        Bus::fake([
            StoreEntityFaceImage::class,
            RemoveEntityFaceImage::class,
        ]);

        /** @var Entity $model */
        $model = Entity::create([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../../assets/image1a.jpg',
        ]);

        // When
        $model->delete();

        // Then
        Bus::assertDispatched(StoreEntityFaceImage::class, 1);
        Bus::assertDispatched(RemoveEntityFaceImage::class, 1);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function aProperCollectionNameIsReturnedWhenNotDefined()
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
    public function entityIdIsReturnedWhenRequestingAMatch()
    {
        // Given
        Bus::fake(StoreEntityFaceImage::class);

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
    public function entityIdIsReturnedWhenRequestingAMatchFromBinary()
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
    public function aCollectionIsRemoved()
    {
        // Given
        Bus::fake(StoreEntityFaceImage::class);

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
    public function aRecordIsForgotten()
    {
        // Given
        /** @var Result $resultDelete */
        $resultDelete = new Result($this->loadResponse('face_delete_success'));

        /** @var Result $resultCreate */
        $resultDetect = new Result($this->loadResponse('face_detect_success'));

        /** @var Result $resultList */
        $resultIndex = new Result($this->loadResponse('image_index_success'));

        /** @var Result $resultList */
        $resultList = new Result($this->loadResponse('collection_list_success'));

        /** @var Result $resultCreate */
        $resultCreate = new Result($this->loadResponse('collection_create_success'));

        /** @var string $faceId */
        $faceId = $resultDelete->get('DeletedFaces')[0];

        /** @var string $file */
        $file = __DIR__.'/../../assets/image1a.jpg';

        /** @var Entity $model */
        $model = Entity::make([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => $file,
        ]);

        /** @var string $collectionName */
        $collectionName = $model->getCollection();

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use (
                $faceId,
                $file,
                $model,
                $collectionName,
                $resultDelete,
                $resultDetect,
                $resultIndex,
                $resultList,
                $resultCreate
            ) {
                $mock->shouldReceive('createCollection')
                    ->with(['CollectionId' => $collectionName])
                    ->andReturn($resultCreate)
                ;

                $mock->shouldReceive('listCollections')
                    ->andReturn($resultList)
                ;

                $mock->shouldReceive('detectFaces')
                    ->with(
                        [
                            'Attributes' => AwsFaceMatchService::IMAGE_INDEXING_RESPONSE_ATTRIBUTE,
                            'Image'      => [
                                'Bytes' => file_get_contents($file),
                            ],
                        ]
                    )
                    ->andReturn($resultDetect)
                ;

                $mock->shouldReceive('indexFaces')
                    ->with(
                        [
                            'CollectionId'        => $collectionName,
                            'DetectionAttributes' => AwsFaceMatchService::IMAGE_INDEXING_RESPONSE_ATTRIBUTE,
                            'ExternalImageId'     => $model->uuid,
                            'Image'               => [
                                'Bytes' => file_get_contents($file),
                            ],
                            'MaxFaces'      => AwsFaceMatchService::MAXIMUM_FACES_TO_PROCESS,
                            'QualityFilter' => AwsFaceMatchService::IMAGE_FILTER_PROCESSING_LEVEL,
                        ]
                    )
                    ->andReturn($resultIndex)
                ;

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

        $model->save();

        // When
        $response = Entity::faceForget($model);

        // Then
        $this->assertTrue(in_array($faceId, $response));
    }
}
