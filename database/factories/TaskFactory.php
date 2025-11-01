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
            'room' => ['Sweep floor', 'Mop floor', 'Dust surfaces', 'Empty trash', 'Make bed', 'Wipe counters', 'Clean mirror'],
            'inventory' => ['Restock soap', 'Restock toilet paper', 'Restock coffee/tea', 'Replace towels'],
        ];

        $type = fake()->randomElement(['room', 'inventory']);
        return [
            'name'        => fake()->randomElement($roomTypeTasks[$type]),
            'is_default'   => $this->faker->boolean(30),
            'type'         => $this->faker->randomElement(['room', 'inventory']),
            'instructions' => $this->faker->boolean(40) ? $this->faker->sentence(10) : null,
        ];
    }
}
