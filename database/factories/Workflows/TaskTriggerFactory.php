<?php

namespace Database\Factories\Workflows;

use App\Enums\Workflows\TaskTriggerType;
use App\Models\Workflows\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workflows\TaskTrigger>
 */
class TaskTriggerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'trigger_type' => TaskTriggerType::ON_DONE,
            'source_task_id' => Task::factory(),
            'target_task_id' => Task::factory(),
        ];
    }
}
