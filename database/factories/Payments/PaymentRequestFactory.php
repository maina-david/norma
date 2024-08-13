<?php

namespace Database\Factories\Payments;

use App\Models\Collaborators\Team;
use App\Models\Workflows\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payments\PaymentRequest>
 */
class PaymentRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'team_id' => Team::factory(),
            'task_id' => Task::factory(),
            'units' => $this->faker->randomFloat(1, 0.5, 2.0),
            'rate_per_unit' => $this->faker->randomFloat(1, 10, 25),
        ];
    }
}
