<?php

namespace Grananda\AwsFaceMatch\Tests\External\Commands;

use Illuminate\Support\Facades\DB;
use Grananda\AwsFaceMatch\Tests\TestCase;
use Grananda\AwsFaceMatch\Models\Collection;
use Grananda\AwsFaceMatch\Tests\Models\Entity;
use Grananda\AwsFaceMatch\Models\FaceMatchEntity;
use Grananda\AwsFaceMatch\Tests\Models\BinEntity;
use Grananda\AwsFaceMatch\Commands\FaceMatchModelIndex;
use Grananda\AwsFaceMatch\Services\AwsFaceMatchCollectionService;

/**
 * Class FaceMatchModelIndexTest.
 *
 * @group external
 * @covers \Grananda\AwsFaceMatch\Commands\FaceMatchModelIndex
 *
 * @package Grananda\AwsFaceMatch\Tests\Unit\Commands
 */
class FaceMatchModelIndexTest extends TestCase
{
    /**
     * @test
     */
    public function allRecordsFromDifferentModelsAreIndexed()
    {
        // Given
        /** @var string $modelUuid1 */
        $modelUuid1 = $this->faker->uuid;

        /** @var string $modelWord1 */
        $modelWord1 = $this->faker->word;

        /** @var string $modelMedia1 */
        $modelMedia1 = __DIR__.'/../../assets/image1a.jpg';

        DB::insert('INSERT INTO entities (uuid, name, media_url) VALUES (?, ?, ?)', [$modelUuid1, $modelWord1, $modelMedia1]);

        /** @var string $modelUuid2 */
        $modelUuid2 = $this->faker->uuid;

        /** @var string $modelWord2 */
        $modelWord2 = $this->faker->word;

        /** @var string $modelMedia2 */
        $modelMedia2 = file_get_contents(__DIR__.'/../../assets/image1a.jpg');

        DB::insert('INSERT INTO bin_entities (uuid, name, media_url) VALUES (?, ?, ?)', [$modelUuid2, $modelWord2, $modelMedia2]);

        /** @var AwsFaceMatchCollectionService $collectionService */
        $collectionService = resolve(AwsFaceMatchCollectionService::class);

        $collectionService->purgeCollections();

        /** @var FaceMatchModelIndex $command */
        $command = resolve(FaceMatchModelIndex::class);

        // When
        $command->handle();

        $response1 = Entity::faceMatch(__DIR__.'/../../assets/image1b.jpg');
        $response2 = BinEntity::faceMatch(__DIR__.'/../../assets/image1a.jpg');

        // Then
        $this->assertEquals($response1->uuid, $modelUuid1);
        $this->assertEquals($response2->uuid, $modelUuid2);

        $this->assertCount(2, Collection::get());
        $this->assertCount(2, FaceMatchEntity::get());

        $collectionService->purgeCollections();
    }
}
