<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Room> */
class RoomFactory extends Factory
{
    protected $model = \App\Models\Room::class;

    public function definition(): array
    {
        return [
            'property_id' => 1, // override in seeder
            'name'        => fake()->randomElement(['Bedroom', 'Kitchen', 'Bathroom', 'Living Room', 'Dining']),
            'is_default'  => false,
        ];
    }
}
