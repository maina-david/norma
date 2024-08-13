<?php

namespace Database\Factories\Workflows;

use App\Enums\Workflows\AutoArchiveOption;
use App\Enums\Workflows\TaskTypeOption;
use App\Enums\Workflows\WorkStatusOption;
use App\Models\Workflows\RatingGroup;
use App\Models\Workflows\TaskRoute;
use App\Models\Workflows\TaskType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TaskType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'colour' => '#ffffff',
            'rating_group_id' => RatingGroup::factory(),
            'task_route_id' => TaskRoute::factory(),
            'is_parent' => true,
            'assignee_can_close' => false,
            'validations' => [],
        ];
    }

    /**
     * Add validation fields.
     *
     * @return $this
     */
    public function withValidations(): self
    {
        return $this->state([
            'validations' => [
                'status' => [
                    'complexity' => TaskTypeOption::NONE->value,
                    'statements' => TaskTypeOption::NONE->value,
                    'citations' => TaskTypeOption::NONE->value,
                    'identified_citations' => TaskTypeOption::NONE->value,
                    'metadata' => TaskTypeOption::NONE->value,
                    'summaries' => TaskTypeOption::NONE->value,
                    'work_highlights' => TaskTypeOption::NONE->value,
                    'work_update_report' => TaskTypeOption::NONE->value,
                    'work' => WorkStatusOption::NONE->value,
                    'work_expression' => WorkStatusOption::NONE->value,
                ],
                'on_todo' => [
                    'auto_archive' => AutoArchiveOption::NONE->value,
                ],
            ],
        ]);
    }
}
