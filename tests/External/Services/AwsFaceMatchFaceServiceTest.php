<?php

namespace Grananda\AwsFaceMatch\Tests\External;

use Exception;
use Aws\Rekognition\RekognitionClient;
use Grananda\AwsFaceMatch\Tests\TestCase;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchFaceService;
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
class AwsFaceMatchFaceServiceTest extends TestCase
{
    /**
     * @test
     *
     * @throws Exception
     */
    public function anImageCanBeIndexed()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var string $subjectId */
        $subjectId = $this->faker->uuid;

        /** @var string $file */
        $file = __DIR__.'/../../assets/image1a.jpg';

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        /** @var AwsFaceMatchFaceService $service */
        $service = resolve(AwsFaceMatchFaceService::class);

        $collectionService->initializeCollection($collectionName);

        // When
        $response = $service->indexFace($collectionName, $subjectId, $file);

        // Then
        $this->assertEquals($subjectId, $response->get('FaceRecords')[0]['Face']['ExternalImageId']);

        $collectionService->purgeCollections();
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function aRemoteImageCanBeIndexed()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var string $subjectId */
        $subjectId = $this->faker->uuid;

        /** @var string $file */
        $file = 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/cc/Natalia_Lafourcade_2018_Gran_Rex_37_%28Cropped%29.jpg/800px-Natalia_Lafourcade_2018_Gran_Rex_37_%28Cropped%29.jpg';

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        /** @var AwsFaceMatchFaceService $service */
        $service = resolve(AwsFaceMatchFaceService::class);

        $collectionService->initializeCollection($collectionName);

        // When
        $response = $service->indexFace($collectionName, $subjectId, $file);

        // Then
        $this->assertEquals($subjectId, $response->get('FaceRecords')[0]['Face']['ExternalImageId']);

        $collectionService->purgeCollections();
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function anBinaryImageCanBeIndexed()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var string $subjectId */
        $subjectId = $this->faker->uuid;

        /** @var string $file */
        $file = base64_encode(file_get_contents(__DIR__.'/../../assets/image1a.jpg'));

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        /** @var AwsFaceMatchFaceService $service */
        $service = resolve(AwsFaceMatchFaceService::class);

        $collectionService->initializeCollection($collectionName);

        // When
        $response = $service->indexFace($collectionName, $subjectId, $file, true);

        // Then
        $this->assertEquals($subjectId, $response->get('FaceRecords')[0]['Face']['ExternalImageId']);

        $collectionService->purgeCollections();
    }

    /**
     * @test
     */
    public function anImageWithMultipleFacesCannotBeIndexed()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var string $subjectId */
        $subjectId = $this->faker->uuid;

        /** @var string $file */
        $file = __DIR__.'/../../assets/image3a.jpg';

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        /** @var AwsFaceMatchFaceService $service */
        $service = resolve(AwsFaceMatchFaceService::class);

        $collectionService->initializeCollection($collectionName);

        // When
        $response = $service->indexFace($collectionName, $subjectId, $file);

        // Then
        $this->assertFalse($response);

        $collectionService->purgeCollections();
    }

    /**
     * @test
     */
    public function anImageWithNoFaceCannotBeIndexed()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var string $subjectId */
        $subjectId = $this->faker->uuid;

        /** @var string $file */
        $file = __DIR__.'/../../assets/image4a.jpg';

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        /** @var AwsFaceMatchFaceService $service */
        $service = resolve(AwsFaceMatchFaceService::class);

        $collectionService->initializeCollection($collectionName);

        // When
        $response = $service->indexFace($collectionName, $subjectId, $file);

        // Then
        $this->assertFalse($response);

        $collectionService->purgeCollections();
    }

    /**
     * @test
     */
    public function aMatchingImageIsRecognized()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var string $subjectId */
        $subjectId = $this->faker->uuid;

        /** @var string $file1 */
        $file1 = __DIR__.'/../../assets/image1a.jpg';

        /** @var string $file2 */
        $file2 = __DIR__.'/../../assets/image1b.jpg';

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        /** @var AwsFaceMatchFaceService $service */
        $service = resolve(AwsFaceMatchFaceService::class);

        $collectionService->initializeCollection($collectionName);

        $service->indexFace($collectionName, $subjectId, $file1);

        // When
        $response = $service->matchFace($collectionName, $file2);

        // Then
        $this->assertEquals($subjectId, $response->get('FaceMatches')[0]['Face']['ExternalImageId']);

        $collectionService->purgeCollections();
    }

    /**
     * @test
     */
    public function anNonMatchingImageIsNotRecognized()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var string $subjectId */
        $subjectId = $this->faker->uuid;

        /** @var string $file1 */
        $file1 = __DIR__.'/../../assets/image1a.jpg';

        /** @var string $file1 */
        $file2 = __DIR__.'/../../assets/image2a.jpg';

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        /** @var AwsFaceMatchFaceService $service */
        $service = resolve(AwsFaceMatchFaceService::class);

        $collectionService->initializeCollection($collectionName);

        $service->indexFace($collectionName, $subjectId, $file1);

        // When
        $response = $service->matchFace($collectionName, $file2);

        // Then
        $this->assertEmpty($response->get('FaceMatches'));

        $collectionService->purgeCollections();
    }

    /**
     * @test
     */
    public function aRecordIsForgotten()
    {
        // Given
        /** @var string $collectionName */
        $collectionName = $this->faker->word;

        /** @var string $subjectId */
        $subjectId = $this->faker->uuid;

        /** @var string $file */
        $file = __DIR__.'/../../assets/image1a.jpg';

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        /** @var AwsFaceMatchFaceService $service */
        $service = resolve(AwsFaceMatchFaceService::class);

        $collectionService->initializeCollection($collectionName);

        $face = $service->indexFace($collectionName, $subjectId, $file);

        // When
        $response = $service->forgetFaces($collectionName,
            [
                $face->get('FaceRecords')[0]['Face']['FaceId'],
            ]
        );

        // Then
        $this->assertEquals($face->get('FaceRecords')[0]['Face']['FaceId'], $response->get('DeletedFaces')[0]);

        $collectionService->purgeCollections();
    }
}
