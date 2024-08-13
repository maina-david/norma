<?php

namespace Database\Factories\Arachno;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Arachno\UpdateEmail>
 */
class UpdateEmailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'subject' => $this->faker->unique()->words(3, true),
            'from' => $this->faker->email(),
            'to' => $this->faker->email(),
        ];
    }
}
