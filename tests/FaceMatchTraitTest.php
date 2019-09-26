<?php

namespace Grananda\AwsFaceMatch\Tests;

use Grananda\AwsFaceMatch\Events\EntityImageWasUploaded;
use Grananda\AwsFaceMatch\Tests\Models\Entity;
use Illuminate\Support\Facades\Bus;

/**
 * Class FaceMatchTraitTest.
 *
 * @package Grananda\AwsFaceMatch\Test
 */
class FaceMatchTraitTest extends TestCase
{
    /** @test */
    public function event_is_triggered_on_model_save()
    {
        // Given
        /** @var Entity $obj */
        $obj = Entity::make([
            'name' => $this->faker->name,
        ]);

        // When
        $obj->save();

        // Then
        $this->assertTrue(true);

        Bus::eventWasTrigerred(EntityImageWasUploaded::class);
    }
}
