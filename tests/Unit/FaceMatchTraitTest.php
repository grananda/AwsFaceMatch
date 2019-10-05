<?php

namespace Grananda\AwsFaceMatch\Tests\Unit;

use Illuminate\Support\Facades\Bus;
use Grananda\AwsFaceMatch\Tests\TestCase;
use Grananda\AwsFaceMatch\Tests\Models\Entity;
use Grananda\AwsFaceMatch\Jobs\EntityImageWasStored;

/**
 * Class FaceMatchTraitTest.
 *
 * @group unit
 *
 * @package Grananda\AwsFaceMatch\Test
 * @covers \Grananda\AwsFaceMatch\Traits\FacialRecognition
 */
class FaceMatchTraitTest extends TestCase
{
    /** @test */
    public function job_is_dispatched_on_model_saved()
    {
        // Given
        Bus::fake(EntityImageWasStored::class);

        /** @var Entity $obj */
        $obj = Entity::create([
            'name' => $this->faker->name,
        ]);

        // When
        $obj->save();

        // Then
        Bus::assertDispatched(EntityImageWasStored::class);
    }
}
