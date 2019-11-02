<?php

namespace Grananda\AwsFaceMatch\Tests\External\Traits;

use Grananda\AwsFaceMatch\Tests\TestCase;
use Grananda\AwsFaceMatch\Tests\Models\Entity;

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
    public function entity_id_is_returned_when_requesting_a_match()
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
    }

    /**
     * @test
     */
    public function false_is_returned_when_requesting_a_no_match()
    {
        // Given
        /** @var string $uuid */
        $uuid = $this->faker->uuid;

        /** @var string $file1 */
        $file1 = __DIR__.'/../../assets/image1a.jpg';

        /** @var string $file2 */
        $file2 = __DIR__.'/../../assets/image2a.jpg';

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
        $this->assertFalse($response);
    }
}