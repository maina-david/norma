<?php

namespace Database\Factories\Collaborators;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionPodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(2, true),
        ];
    }
}
