<?php

namespace Grananda\AwsFaceMatch\Tests\External\Traits;

use Grananda\AwsFaceMatch\Tests\TestCase;
use Grananda\AwsFaceMatch\Models\Collection;
use Grananda\AwsFaceMatch\Tests\Models\Entity;
use Grananda\AwsFaceMatch\Models\FaceMatchEntity;
use Grananda\AwsFaceMatch\Tests\Models\BinEntity;

/**
 * Class AwsFaceMatchServiceTest.
 * https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.Rekognition.RekognitionClient.html.
 *
 * @package Grananda\AwsFaceMatch\Tests
 *
 * @group external
 * @covers \Grananda\AwsFaceMatch\Traits\FacialRecognition
 */
class FaceMatchTraitTest extends TestCase
{
    /**
     * @test
     */
    public function entityCanBeCreated()
    {
        // Given
        /** @var string $uuid */
        $uuid = $this->faker->uuid;

        /** @var string $file1 */
        $file1 = __DIR__.'/../../assets/image1a.jpg';

        /** @var string $file2 */
        $file2 = __DIR__.'/../../assets/image1b.jpg';

        Entity::purgeCollection();

        // When
        /** @var Entity $model */
        $model = Entity::create([
            'uuid'      => $uuid,
            'name'      => $this->faker->name,
            'media_url' => $file1,
        ]);

        // Then
        $this->assertCount(1, Collection::get());
        $this->assertCount(1, FaceMatchEntity::get());

        Entity::purgeCollection();
    }

    /**
     * @test
     */
    public function entityIdIsReturnedWhenRequestingAMatch()
    {
        // Given
        /** @var string $uuid */
        $uuid = $this->faker->uuid;

        /** @var string $file1 */
        $file1 = __DIR__.'/../../assets/image1a.jpg';

        /** @var string $file2 */
        $file2 = __DIR__.'/../../assets/image1b.jpg';

        Entity::purgeCollection();

        /** @var Entity $model */
        $model = Entity::create([
            'uuid'      => $uuid,
            'name'      => $this->faker->name,
            'media_url' => $file1,
        ]);

        // When
        $response = Entity::faceMatch($file2);

        // Then
        $this->assertEquals($model->uuid, $response->uuid);

        $this->assertCount(1, Collection::get());
        $this->assertCount(1, FaceMatchEntity::get());

        Entity::purgeCollection();
    }

    /**
     * @test
     */
    public function entityIdIsReturnedWhenRequestingABinMatch()
    {
        // Given
        /** @var string $uuid */
        $uuid = $this->faker->uuid;

        /** @var string $file1 */
        $file1 = __DIR__.'/../../assets/image1a.jpg';

        /** @var string $file2 */
        $file2 = __DIR__.'/../../assets/image1b.jpg';

        Entity::purgeCollection();

        /** @var Entity $model */
        $model = BinEntity::create([
            'uuid'      => $uuid,
            'name'      => $this->faker->name,
            'media_url' => file_get_contents($file1),
        ]);

        // When
        $response = BinEntity::faceMatch($file2);

        // Then
        $this->assertEquals($model->uuid, $response->uuid);

        $this->assertCount(1, Collection::get());
        $this->assertCount(1, FaceMatchEntity::get());

        Entity::purgeCollection();
    }

    /**
     * @test
     */
    public function falseIsReturnedWhenRequestingANoMatch()
    {
        // Given
        /** @var string $uuid */
        $uuid = $this->faker->uuid;

        /** @var string $file1 */
        $file1 = __DIR__.'/../../assets/image1a.jpg';

        /** @var string $file2 */
        $file2 = __DIR__.'/../../assets/image2a.jpg';

        Entity::purgeCollection();

        Entity::create([
            'uuid'      => $uuid,
            'name'      => $this->faker->name,
            'media_url' => $file1,
        ]);

        // When
        $response = Entity::faceMatch($file2);

        // Then
        $this->assertFalse($response);

        $this->assertCount(1, Collection::get());
        $this->assertCount(1, FaceMatchEntity::get());

        Entity::purgeCollection();
    }

    /**
     * @test
     */
    public function entityCanBeForgotten()
    {
        // Given
        /** @var string $uuid */
        $uuid = $this->faker->uuid;

        /** @var string $file */
        $file = __DIR__.'/../../assets/image1a.jpg';

        Entity::purgeCollection();

        /** @var Entity $model */
        $model = Entity::create([
            'uuid'      => $uuid,
            'name'      => $this->faker->name,
            'media_url' => $file,
        ]);

        /** @var FaceMatchEntity $face */
        $face = FaceMatchEntity::get()->first();

        // When
        $response = Entity::faceForget($model);

        // Then
        $this->assertTrue(in_array($face->face_id, $response));

        $this->assertCount(1, Collection::get());
        $this->assertCount(1, FaceMatchEntity::get());

        Entity::purgeCollection();
    }

    /**
     * @test
     */
    public function entityCanBeRemoved()
    {
        // Given
        /** @var string $uuid */
        $uuid = $this->faker->uuid;

        /** @var string $file */
        $file = __DIR__.'/../../assets/image1a.jpg';

        Entity::purgeCollection();

        /** @var Entity $model */
        $model = Entity::create([
            'uuid'      => $uuid,
            'name'      => $this->faker->name,
            'media_url' => $file,
        ]);

        /** @var FaceMatchEntity $face */
        $face = FaceMatchEntity::get()->first();

        // When
        $model->delete();

        // Then
        $this->assertCount(1, Collection::get());
        $this->assertCount(0, FaceMatchEntity::get());

        Entity::purgeCollection();
    }
}
