<?php

namespace Grananda\AwsFaceMatch\Tests\Unit;

use Mockery;
use Exception;
use Aws\Result;
use Aws\Rekognition\RekognitionClient;
use Grananda\AwsFaceMatch\Tests\TestCase;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchService;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchFaceService;
use Grananda\AwsFaceMatch\Services\AwsRekognitionClientFactory;

/**
 * Class AwsFaceMatchServiceTest.
 * https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.Rekognition.RekognitionClient.html.
 *
 * @package Grananda\AwsFaceMatch\Tests
 *
 * @group unit
 * @covers \Grananda\AwsFaceMatch\Services\AwsFaceMatchService
 */
class AwsFaceMatchFaceServiceTest extends TestCase
{
    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @throws Exception
     */
    public function an_image_can_be_indexed()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var string $file */
        $file = __DIR__.'/../../assets/image1a.jpg';

        /** @var Result $resultCreate */
        $resultDetect = new Result($this->loadResponse('face_detect_success'));

        /** @var Result $resultList */
        $resultIndex = new Result($this->loadResponse('image_index_success'));

        /** @var string $subjectId */
        $subjectId = $resultIndex->get('FaceRecords')[0]['Face']['ExternalImageId'];

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use ($collectionName, $subjectId, $file, $resultDetect, $resultIndex) {
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
                            'ExternalImageId'     => $subjectId,
                            'Image'               => [
                                'Bytes' => file_get_contents($file),
                            ],
                            'MaxFaces'      => AwsFaceMatchService::MAXIMUM_FACES_TO_PROCESS,
                            'QualityFilter' => AwsFaceMatchService::IMAGE_FILTER_PROCESSING_LEVEL,
                        ]
                    )
                    ->andReturn($resultIndex)
                ;
            });

        $this->mock('alias:'.AwsRekognitionClientFactory::class, function ($mock) use ($rekognitionClientMock) {
            $mock->shouldReceive('instantiate')
                ->andReturn($rekognitionClientMock)
            ;
        });

        /** @var AwsFaceMatchFaceService $service */
        $service = resolve(AwsFaceMatchFaceService::class);

        // When
        $response = $service->indexFace($collectionName, $subjectId, $file);

        // Then
        $this->assertEquals($subjectId, $response->get('FaceRecords')[0]['Face']['ExternalImageId']);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @throws Exception
     */
    public function an_binary_image_can_be_indexed()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var string $file */
        $file = file_get_contents(__DIR__.'/../../assets/image1a.jpg');

        /** @var Result $resultCreate */
        $resultDetect = new Result($this->loadResponse('face_detect_success'));

        /** @var Result $resultList */
        $resultIndex = new Result($this->loadResponse('image_index_success'));

        /** @var string $subjectId */
        $subjectId = $resultIndex->get('FaceRecords')[0]['Face']['ExternalImageId'];

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use ($collectionName, $subjectId, $file, $resultDetect, $resultIndex) {
                $mock->shouldReceive('detectFaces')
                    ->with(
                        [
                            'Attributes' => AwsFaceMatchService::IMAGE_INDEXING_RESPONSE_ATTRIBUTE,
                            'Image'      => [
                                'Bytes' => base64_decode($file),
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
                            'ExternalImageId'     => $subjectId,
                            'Image'               => [
                                'Bytes' => base64_decode($file),
                            ],
                            'MaxFaces'      => AwsFaceMatchService::MAXIMUM_FACES_TO_PROCESS,
                            'QualityFilter' => AwsFaceMatchService::IMAGE_FILTER_PROCESSING_LEVEL,
                        ]
                    )
                    ->andReturn($resultIndex)
                ;
            });

        $this->mock('alias:'.AwsRekognitionClientFactory::class, function ($mock) use ($rekognitionClientMock) {
            $mock->shouldReceive('instantiate')
                ->andReturn($rekognitionClientMock)
            ;
        });

        /** @var AwsFaceMatchFaceService $service */
        $service = resolve(AwsFaceMatchFaceService::class);

        // When
        $response = $service->indexFace($collectionName, $subjectId, $file, true);

        // Then
        $this->assertEquals($subjectId, $response->get('FaceRecords')[0]['Face']['ExternalImageId']);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @throws Exception
     */
    public function an_remote_image_can_be_indexed()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var string $file */
        $file = 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/cc/Natalia_Lafourcade_2018_Gran_Rex_37_%28Cropped%29.jpg/800px-Natalia_Lafourcade_2018_Gran_Rex_37_%28Cropped%29.jpg';

        /** @var Result $resultCreate */
        $resultDetect = new Result($this->loadResponse('face_detect_success'));

        /** @var Result $resultList */
        $resultIndex = new Result($this->loadResponse('image_index_success'));

        /** @var string $subjectId */
        $subjectId = $resultIndex->get('FaceRecords')[0]['Face']['ExternalImageId'];

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use ($collectionName, $subjectId, $file, $resultDetect, $resultIndex) {
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
                            'ExternalImageId'     => $subjectId,
                            'Image'               => [
                                'Bytes' => file_get_contents($file),
                            ],
                            'MaxFaces'      => AwsFaceMatchService::MAXIMUM_FACES_TO_PROCESS,
                            'QualityFilter' => AwsFaceMatchService::IMAGE_FILTER_PROCESSING_LEVEL,
                        ]
                    )
                    ->andReturn($resultIndex)
                ;
            });

        $this->mock('alias:'.AwsRekognitionClientFactory::class, function ($mock) use ($rekognitionClientMock) {
            $mock->shouldReceive('instantiate')
                ->andReturn($rekognitionClientMock)
            ;
        });

        /** @var AwsFaceMatchFaceService $service */
        $service = resolve(AwsFaceMatchFaceService::class);

        // When
        $response = $service->indexFace($collectionName, $subjectId, $file);

        // Then
        $this->assertEquals($subjectId, $response->get('FaceRecords')[0]['Face']['ExternalImageId']);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function an_image_with_multiple_faces_cannot_be_indexed()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var string $file */
        $file = __DIR__.'/../../assets/image3a.jpg';

        /** @var Result $resultCreate */
        $resultDetect = new Result($this->loadResponse('face_detect_multiple_success'));

        /** @var Result $resultList */
        $resultIndex = new Result($this->loadResponse('image_index_success'));

        /** @var string $subjectId */
        $subjectId = $resultIndex->get('FaceRecords')[0]['Face']['ExternalImageId'];

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use ($collectionName, $subjectId, $file, $resultDetect, $resultIndex) {
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
            });

        $this->mock('alias:'.AwsRekognitionClientFactory::class, function ($mock) use ($rekognitionClientMock) {
            $mock->shouldReceive('instantiate')
                ->andReturn($rekognitionClientMock)
            ;
        });

        /** @var AwsFaceMatchFaceService $service */
        $service = resolve(AwsFaceMatchFaceService::class);

        // When
        $response = $service->indexFace($collectionName, $subjectId, $file);

        // Then
        $this->assertFalse($response);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function an_image_with_no_face_cannot_be_indexed()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var string $file */
        $file = __DIR__.'/../../assets/image4a.jpg';

        /** @var Result $resultCreate */
        $resultDetect = new Result($this->loadResponse('face_detect_none_success'));

        /** @var Result $resultList */
        $resultIndex = new Result($this->loadResponse('image_index_success'));

        /** @var string $subjectId */
        $subjectId = $resultIndex->get('FaceRecords')[0]['Face']['ExternalImageId'];

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use ($collectionName, $subjectId, $file, $resultDetect, $resultIndex) {
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
            });

        $this->mock('alias:'.AwsRekognitionClientFactory::class, function ($mock) use ($rekognitionClientMock) {
            $mock->shouldReceive('instantiate')
                ->andReturn($rekognitionClientMock)
            ;
        });

        /** @var AwsFaceMatchFaceService $service */
        $service = resolve(AwsFaceMatchFaceService::class);

        // When
        $response = $service->indexFace($collectionName, $subjectId, $file);

        // Then
        $this->assertFalse($response);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function a_matching_image_is_recognized()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var string $file */
        $file = __DIR__.'/../../assets/image1a.jpg';

        /** @var Result $resultCreate */
        $resultMatch = new Result($this->loadResponse('face_match_success'));

        /** @var string $subjectId */
        $subjectId = $resultMatch->get('FaceMatches')[0]['Face']['ExternalImageId'];

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use ($collectionName, $file, $resultMatch) {
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

        /** @var AwsFaceMatchFaceService $service */
        $service = resolve(AwsFaceMatchFaceService::class);

        // When
        $response = $service->matchFace($collectionName, $file);

        // Then
        $this->assertEquals($subjectId, $response->get('FaceMatches')[0]['Face']['ExternalImageId']);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function an_non_matching_image_is_not_recognized()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var string $file */
        $file = __DIR__.'/../../assets/image1a.jpg';

        /** @var Result $resultCreate */
        $resultMatch = new Result($this->loadResponse('face_match_fail'));

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use ($collectionName, $file, $resultMatch) {
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

        /** @var AwsFaceMatchFaceService $service */
        $service = resolve(AwsFaceMatchFaceService::class);

        // When
        $response = $service->matchFace($collectionName, $file);

        // Then
        $this->assertEmpty($response->get('FaceMatches'));
    }
}
