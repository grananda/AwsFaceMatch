<?php

namespace Grananda\AwsFaceMatch\Tests\Unit\Jobs;

use Mockery;
use Aws\Result;
use Aws\Rekognition\RekognitionClient;
use Grananda\AwsFaceMatch\Tests\TestCase;
use Grananda\AwsFaceMatch\Tests\Models\Entity;
use Grananda\AwsFaceMatch\Jobs\StoreEntityFaceImage;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchService;
use Grananda\AwsFaceMatch\Services\AwsRekognitionClientFactory;

/**
 * Class StoreEntityFaceImageTest.
 *
 * @group unit
 * @covers \Grananda\AwsFaceMatch\Jobs\StoreEntityFaceImage
 *
 * @package Grananda\AwsFaceMatch\Tests\Unit
 */
class StoreEntityFaceImageTest extends TestCase
{
    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function model_image_is_indexed_in_collection()
    {
        // Given
        /** @var Entity $model */
        $model = Entity::make([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../../assets/image1a.jpg',
        ]);

        /** @var string $collectionName */
        $collectionName = $model->getCollection();

        /** @var string $file */
        $file = $model->getMediaFieldValue();

        /** @var string $subjectId */
        $subjectId = $model->getIdentifierValue();

        /** @var Result $resultList */
        $resultList = new Result($this->loadResponse('collection_list_success'));

        /** @var Result $resultCreate */
        $resultCreate = new Result($this->loadResponse('collection_create_success'));

        /** @var Result $resultCreate */
        $resultDetect = new Result($this->loadResponse('face_detect_success'));

        /** @var Result $resultList */
        $resultIndex = new Result($this->loadResponse('image_index_success'));

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use (
                $collectionName,
                $file,
                $subjectId,
                $resultList,
                $resultCreate,
                $resultDetect,
                $resultIndex
            ) {
                $mock->shouldReceive('createCollection')
                    ->with(['CollectionId' => $collectionName])
                    ->andReturn($resultCreate)
                    ->times(1)
                ;

                $mock->shouldReceive('listCollections')
                    ->andReturn($resultList)
                    ->times(1)
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
                    ->times(1)
                ;

                $mock->shouldReceive('indexFaces')
                    ->with(
                        [
                            'CollectionId'        => $collectionName,
                            'DetectionAttributes' => AwsFaceMatchService::IMAGE_INDEXING_RESPONSE_ATTRIBUTE,
                            'ExternalImageId'     => $subjectId,
                            'Image'               => [
                                'Bytes' => file_get_contents($file),
                            ],
                            'MaxFaces'      => AwsFaceMatchService::MAXIMUM_FACES_TO_PROCESS,
                            'QualityFilter' => AwsFaceMatchService::IMAGE_FILTER_PROCESSING_LEVEL,
                        ]
                    )
                    ->andReturn($resultIndex)
                    ->times(1)
                ;
            });

        $this->mock('alias:'.AwsRekognitionClientFactory::class, function ($mock) use ($rekognitionClientMock) {
            $mock->shouldReceive('instantiate')
                ->andReturn($rekognitionClientMock)
            ;
        });

        // When
        StoreEntityFaceImage::dispatch(
            $model->getCollection(),
            $model->getIdentifierValue(),
            $model->getMediaFieldValue()
        );

        // Then
        $this->assertTrue(true);
    }
}
