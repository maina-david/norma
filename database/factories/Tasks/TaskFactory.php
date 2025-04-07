<?php

namespace Database\Factories\Tasks;

use App\Enums\Tasks\TaskPriority;
use App\Enums\Tasks\TaskStatus;
use App\Models\Customer\Norma;
use App\Models\Tasks\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->unique()->randomHtml(1, 1),
            'place_id' => Norma::factory(),
            'taskable_type' => 'place',
            'task_status' => TaskStatus::notStarted()->value,
            'priority' => TaskPriority::low()->value,
        ];
    }
}
