<?php

namespace Database\Factories\Workflows;

use App\Enums\Tasks\TaskPriority;
use App\Enums\Workflows\MonitoringTaskFrequency;
use App\Models\Collaborators\Group;
use App\Models\Workflows\Board;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workflows\MonitoringTaskConfig>
 */
class MonitoringTaskConfigFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'board_id' => Board::factory(),
            'group_id' => Group::factory(),
            'frequency' => 1,
            'start_day' => 1,
            'frequency_quantity' => MonitoringTaskFrequency::WEEK->value,
            'priority' => TaskPriority::high()->value,
            'last_run' => now()->toDateString(),
            'enabled' => true,
        ];
    }
}
