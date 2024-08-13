<?php

namespace Database\Factories\Workflows;

use App\Enums\Workflows\WizardStep;
use App\Models\Workflows\Board;
use App\Models\Workflows\TaskType;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Board::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(8, true),
            'parent_task_type_id' => TaskType::factory(),
            'source_document_required' => false,
            'wizard_steps' => [WizardStep::CREATE_WORK->value],
        ];
    }
}
