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
    public function a_collection_can_be_created()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        $collectionService->purgeCollections();

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
    public function can_remove_a_single_collection()
    {
        // Given
        $collectionName = $this->faker->word;

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        $collectionService->purgeCollections();

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
    public function cannot_describe_an_empty_collection()
    {
        // Given
        $collectionName = $this->faker->word;

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        $collectionService->purgeCollections();

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
    public function can_remove_all_collections()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        $collectionService->purgeCollections();

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
    public function cannot_remove_empty_collection_list()
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
