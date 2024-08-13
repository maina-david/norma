<?php

namespace Database\Factories\Workflows;

use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Document::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(8, true),
            'document_type' => 1,
            'work_expression_id' => WorkExpression::factory(),
        ];
    }
}
