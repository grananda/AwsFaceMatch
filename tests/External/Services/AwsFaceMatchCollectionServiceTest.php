<?php

namespace Grananda\AwsFaceMatch\Tests\External\Services;

use Exception;
use Aws\Result;
use Grananda\AwsFaceMatch\Tests\TestCase;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchCollectionService;

/**
 * Class AwsFaceMatchServiceTest.
 * https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.Rekognition.RekognitionClient.html.
 *
 * @package Grananda\AwsFaceMatch\Tests
 *
 * @group external
 * @covers \Grananda\AwsFaceMatch\Services\AwsFaceMatchService
 */
class AwsFaceMatchCollectionServiceTest extends TestCase
{
    /**
     * @test
     *
     * @throws Exception
     */
    public function aCollectionCanBeCreated()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        /** @var AwsFaceMatchCollectionService $service */
        $service = resolve(AwsFaceMatchCollectionService::class);

        // When
        /** @var Result $response */
        $response = $service->initializeCollection($collectionName);

        // Then
        $this->assertEquals(200, $response->get('StatusCode'));

        $collectionService->purgeCollections();
    }

    /**
     * @test
     */
    public function canRemoveASingleCollection()
    {
        // Given
        $collectionName = $this->faker->word;

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        /** @var AwsFaceMatchCollectionService $service */
        $service = resolve(AwsFaceMatchCollectionService::class);

        $service->initializeCollection($collectionName);

        // When
        $response = $service->deleteCollection($collectionName);

        // Then
        $this->assertEquals(200, $response->get('StatusCode'));

        $collectionService->purgeCollections();
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function cannotDescribeAnEmptyCollection()
    {
        // Given
        $collectionName = $this->faker->word;

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        /** @var AwsFaceMatchCollectionService $service */
        $service = resolve(AwsFaceMatchCollectionService::class);

        $service->initializeCollection($collectionName);

        // When
        $response = $service->describeCollection($collectionName);

        // Then
        $this->assertEquals(0, $response->get('FaceCount'));

        $collectionService->purgeCollections();
    }

    /**
     * @test
     */
    public function canRemoveAllCollections()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        $collectionService->initializeCollection($collectionName);

        /** @var AwsFaceMatchCollectionService $service */
        $service = resolve(AwsFaceMatchCollectionService::class);

        // When
        $response = $service->purgeCollections();

        // Then
        $this->assertTrue($response);
    }

    /**
     * @test
     */
    public function cannotRemoveEmptyCollectionList()
    {
        // Given
        /** @var AwsFaceMatchCollectionService $service */
        $service = resolve(AwsFaceMatchCollectionService::class);

        // When
        $response = $service->purgeCollections();

        // Then
        $this->assertFalse($response);
    }
}
