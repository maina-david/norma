<?php

namespace Database\Factories\Compilation;

use App\Enums\Compilation\ContextQuestionCategory;
use App\Models\Compilation\ContextQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContextQuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ContextQuestion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'prefix' => $this->faker->unique()->words(3, true),
            'predicate' => $this->faker->unique()->words(3, true),
            'pre_object' => $this->faker->unique()->words(3, true),
            'object' => $this->faker->unique()->words(3, true),
            'post_object' => $this->faker->unique()->words(3, true),
            'category_id' => $this->faker->randomElement(ContextQuestionCategory::options()),
        ];
    }
}
