<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Task> */
class TaskFactory extends Factory
{
    protected $model = \App\Models\Task::class;

    public function definition(): array
    {
        $roomTypeTasks = [
            'room' => ['Sweep floor','Mop floor','Dust surfaces','Empty trash','Make bed','Wipe counters','Clean mirror'],
            'inventory' => ['Restock soap','Restock toilet paper','Restock coffee/tea','Replace towels'],
        ];

        $type = fake()->randomElement(['room','inventory']);
        return [
            'property_id' => null, // set in seeder to current property
            'room_id'     => 1, // override
            'name'        => fake()->randomElement($roomTypeTasks[$type]),
            'is_default'  => false,
            'type'        => $type,
        ];
    }
}
