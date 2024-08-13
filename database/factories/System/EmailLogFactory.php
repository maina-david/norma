<?php

namespace Database\Factories\System;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\System\EmailLog>
 */
class EmailLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'to' => $this->faker->email(),
            'from' => $this->faker->email(),
            'subject' => $this->faker->unique()->words(3, true),
            'body' => $this->faker->unique()->randomHtml(1, 1),
        ];
    }
}
