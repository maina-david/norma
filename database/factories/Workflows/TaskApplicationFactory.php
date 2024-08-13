<?php

namespace Database\Factories\Workflows;

use App\Models\Collaborators\Collaborator;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskApplicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TaskApplication::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'task_id' => Task::factory(),
            'user_id' => Collaborator::factory(),
        ];
    }
}
