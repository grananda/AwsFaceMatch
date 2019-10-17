<?php

namespace Grananda\AwsFaceMatch\Tests\Unit\Commands;

use Mockery;
use Aws\Result;
use Illuminate\Support\Facades\Bus;
use Aws\Rekognition\RekognitionClient;
use Grananda\AwsFaceMatch\Tests\TestCase;
use Grananda\AwsFaceMatch\Tests\Models\Entity;
use Grananda\AwsFaceMatch\Tests\Models\OtherEntity;
use Grananda\AwsFaceMatch\Jobs\StoreEntityFaceImage;
use Grananda\AwsFaceMatch\Commands\FaceMatchModelIndex;
use Grananda\AwsFaceMatch\Services\AwsRekognitionClientFactory;

class FaceMatchModelIndexTest extends TestCase
{
    /**
     * @test
     */
    public function all_records_from_different_models_are_indexed()
    {
        // Given
        Bus::fake(StoreEntityFaceImage::class);

        Entity::create([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../../assets/image1a.jpg',
        ]);

        Entity::create([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../../assets/image1a.jpg',
        ]);

        OtherEntity::create([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../../assets/image1a.jpg',
        ]);

        OtherEntity::create([
            'uuid'      => $this->faker->uuid,
            'name'      => $this->faker->name,
            'media_url' => __DIR__.'/../../assets/image1a.jpg',
        ]);

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
            function ($mock) use ($resultCreate, $resultList, $resultDetect, $resultIndex) {
                $mock->shouldReceive('createCollection')
                    ->andReturn($resultCreate)
                    ->times(2)
                ;

                $mock->shouldReceive('listCollections')
                    ->andReturn($resultList)
                    ->times(2)
                ;

                $mock->shouldReceive('detectFaces')
                    ->andReturn($resultDetect)
                    ->times(4)
                ;

                $mock->shouldReceive('indexFaces')
                    ->andReturn($resultIndex)
                    ->times(4)
                ;
            });

        $this->mock('alias:'.AwsRekognitionClientFactory::class, function ($mock) use ($rekognitionClientMock) {
            $mock->shouldReceive('instantiate')
                ->andReturn($rekognitionClientMock)
            ;
        });

        /** @var FaceMatchModelIndex $command */
        $command = resolve(FaceMatchModelIndex::class);

        // When
        $command->handle();

        // Then
        $this->assertTrue(true);
    }
}
