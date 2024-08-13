<?php

namespace Database\Factories\Arachno;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Arachno\ChangeAlert>
 */
class ChangeAlertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(3, true),
            'url' => $this->faker->unique()->url(),
            'previous' => $this->faker->unique()->words(3, true),
            'current' => $this->faker->unique()->words(3, true),
        ];
    }
}
