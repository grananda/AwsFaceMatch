<?php

namespace Grananda\AwsFaceMatch\Tests\External;

use Exception;
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
    public function an_image_can_be_indexed()
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
    public function a_remote_image_can_be_indexed()
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
     */
    public function an_image_with_multiple_faces_cannot_be_indexed()
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
    public function an_image_with_no_face_cannot_be_indexed()
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
    public function a_matching_image_is_recognized()
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
    public function an_non_matching_image_is_not_recognized()
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
}
