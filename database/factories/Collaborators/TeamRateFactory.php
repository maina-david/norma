<?php

namespace Database\Factories\Collaborators;

use App\Models\Collaborators\Team;
use App\Models\Workflows\TaskType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Collaborators\TeamRate>
 */
class TeamRateFactory extends Factory
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
            'for_task_type_id' => TaskType::factory(),
            'rate_per_unit' => $this->faker->randomFloat(1, 10, 25),
            'unit_type_singular' => 'Hour',
            'unit_type_plural' => 'Hours',
        ];
    }
}
