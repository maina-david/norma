<?php

namespace Database\Factories\Workflows;

use App\Enums\Workflows\TaskStatus;
use App\Models\Workflows\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workflows\TaskTransition>
 */
class TaskTransitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'task_id' => Task::factory()->state(['task_status' => TaskStatus::inProgress()->value]),
            'task_status' => TaskStatus::inProgress()->value,
            'transitioned_field' => 'task_status',
        ];
    }
}
