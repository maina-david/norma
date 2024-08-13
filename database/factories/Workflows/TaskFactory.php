<?php

namespace Database\Factories\Workflows;

use App\Enums\Workflows\TaskComplexity;
use App\Enums\Workflows\TaskPriority;
use App\Enums\Workflows\TaskStatus;
use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\Group;
use App\Models\Workflows\Board;
use App\Models\Workflows\Document;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskType;
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
     * @return array
     */
    public function definition()
    {
        return [
            'board_id' => Board::factory(),
            'task_type_id' => TaskType::factory(),
            'group_id' => Group::factory(),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'task_status' => TaskStatus::todo()->value,
            'user_id' => Collaborator::factory(),
            'manager_id' => Collaborator::factory(),
            'priority' => TaskPriority::medium(),
            'due_date' => now()->addWeek(),
            'requires_rating' => false,
            'author_id' => Collaborator::factory(),
            'complexity' => $this->faker->randomElement(TaskComplexity::forSelector())['value'],
            'units' => random_int(1, 5),
            'document_id' => Document::factory(),
        ];
    }
}
