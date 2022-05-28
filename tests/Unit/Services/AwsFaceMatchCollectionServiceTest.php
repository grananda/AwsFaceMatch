<?php

namespace Grananda\AwsFaceMatch\Tests\Unit\Services;

use Mockery;
use Exception;
use Aws\Result;
use Aws\Rekognition\RekognitionClient;
use Grananda\AwsFaceMatch\Tests\TestCase;
use Grananda\AwsFaceMatch\Services\AwsRekognitionClientFactory;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchCollectionService;

/**
 * Class AwsFaceMatchServiceTest.
 * https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.Rekognition.RekognitionClient.html.
 *
 * @package Grananda\AwsFaceMatch\Tests
 *
 * @group unit
 * @covers \Grananda\AwsFaceMatch\Services\AwsFaceMatchService
 */
class AwsFaceMatchCollectionServiceTest extends TestCase
{
    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @throws Exception
     */
    public function aCollectionCanBeCreated()
    {
        // Given
        /** @var Result $resultList */
        $resultList = new Result($this->loadResponse('collection_list_success'));

        /** @var Result $resultCreate */
        $resultCreate = new Result($this->loadResponse('collection_create_success'));

        /** @var string $collectionName */
        $collectionName = 'new-collection';

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use ($collectionName, $resultList, $resultCreate) {
                $mock->shouldReceive('createCollection')
                    ->with(['CollectionId' => $collectionName])
                    ->andReturn($resultCreate)
                ;

                $mock->shouldReceive('listCollections')
                    ->andReturn($resultList)
                ;
            });

        $this->mock('alias:'.AwsRekognitionClientFactory::class, function ($mock) use ($rekognitionClientMock) {
            $mock->shouldReceive('instantiate')
                ->andReturn($rekognitionClientMock)
            ;
        });

        /** @var AwsFaceMatchCollectionService $service */
        $service = resolve(AwsFaceMatchCollectionService::class);

        // When
        /** @var Result $response */
        $response = $service->initializeCollection($collectionName);

        // Then
        $this->assertEquals(200, $response->get('StatusCode'));
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function canRemoveAllCollections()
    {
        // Given
        /** @var Result $resultList */
        $resultList = new Result($this->loadResponse('collection_list_success'));

        /** @var Result $resultCreate */
        $resultDelete = new Result($this->loadResponse('collection_delete_success'));

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use ($resultList, $resultDelete) {
                $mock->shouldReceive('listCollections')
                    ->andReturn($resultList)
                    ->times(1)
                ;

                $mock->shouldReceive('deleteCollection')
                    ->andReturn($resultDelete)
                    ->times(sizeof($resultList->toArray()['CollectionIds']))
                ;
            });

        $this->mock('alias:'.AwsRekognitionClientFactory::class, function ($mock) use ($rekognitionClientMock) {
            $mock->shouldReceive('instantiate')
                ->andReturn($rekognitionClientMock)
            ;
        });

        /** @var AwsFaceMatchCollectionService $service */
        $service = resolve(AwsFaceMatchCollectionService::class);

        // When
        $response = $service->purgeCollections();

        // Then
        $this->assertTrue($response);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function cannotRemoveEmptyCollectionList()
    {
        // Given
        /** @var Result $resultList */
        $resultList = new Result($this->loadResponse('collection_list_none_success'));

        /** @var Result $resultCreate */
        $resultDelete = new Result($this->loadResponse('collection_delete_success'));

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use ($resultList, $resultDelete) {
                $mock->shouldReceive('listCollections')
                    ->andReturn($resultList)
                    ->times(1)
                ;

                $mock->shouldReceive('deleteCollection')
                    ->andReturn($resultDelete)
                    ->times(0)
                ;
            });

        $this->mock('alias:'.AwsRekognitionClientFactory::class, function ($mock) use ($rekognitionClientMock) {
            $mock->shouldReceive('instantiate')
                ->andReturn($rekognitionClientMock)
            ;
        });

        /** @var AwsFaceMatchCollectionService $service */
        $service = resolve(AwsFaceMatchCollectionService::class);

        // When
        $response = $service->purgeCollections();

        // Then
        $this->assertFalse($response);
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function canRemoveASingleCollection()
    {
        // Given
        $collectionName = $this->faker->word;

        /** @var Result $resultCreate */
        $resultDelete = new Result($this->loadResponse('collection_delete_success'));

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use ($resultDelete, $collectionName) {
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

        /** @var AwsFaceMatchCollectionService $service */
        $service = resolve(AwsFaceMatchCollectionService::class);

        // When
        $response = $service->deleteCollection($collectionName);

        // Then
        $this->assertEquals(200, $response->get('StatusCode'));
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @throws Exception
     */
    public function canDescribeACollection()
    {
        // Given
        $collectionName = $this->faker->word;

        /** @var Result $resultCreate */
        $resultDescription = new Result($this->loadResponse('collection_description_success'));

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use ($resultDescription, $collectionName) {
                $mock->shouldReceive('describeCollection')
                    ->with(
                        [
                            'CollectionId' => $collectionName,
                        ]
                    )
                    ->andReturn($resultDescription)
                    ->times(1)
                ;
            });

        $this->mock('alias:'.AwsRekognitionClientFactory::class, function ($mock) use ($rekognitionClientMock) {
            $mock->shouldReceive('instantiate')
                ->andReturn($rekognitionClientMock)
            ;
        });

        /** @var AwsFaceMatchCollectionService $service */
        $service = resolve(AwsFaceMatchCollectionService::class);

        // When
        $response = $service->describeCollection($collectionName);

        // Then
        $this->assertGreaterThan(0, $response->get('FaceCount'));
    }

    /**
     * @test
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @throws Exception
     */
    public function cannotDescribeAnEmptyCollection()
    {
        // Given
        $collectionName = $this->faker->word;

        /** @var Result $resultCreate */
        $resultDescription = new Result($this->loadResponse('collection_description_empty_success'));

        /** @var Mockery $rekognitionClientMock */
        $rekognitionClientMock = $this->mock(RekognitionClient::class,
            function ($mock) use ($resultDescription, $collectionName) {
                $mock->shouldReceive('describeCollection')
                    ->with(
                        [
                            'CollectionId' => $collectionName,
                        ]
                    )
                    ->andReturn($resultDescription)
                    ->times(1)
                ;
            });

        $this->mock('alias:'.AwsRekognitionClientFactory::class, function ($mock) use ($rekognitionClientMock) {
            $mock->shouldReceive('instantiate')
                ->andReturn($rekognitionClientMock)
            ;
        });

        /** @var AwsFaceMatchCollectionService $service */
        $service = resolve(AwsFaceMatchCollectionService::class);

        // When
        $response = $service->describeCollection($collectionName);

        // Then
        $this->assertEquals(0, $response->get('FaceCount'));
    }
}
