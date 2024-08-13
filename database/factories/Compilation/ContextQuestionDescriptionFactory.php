<?php

namespace Database\Factories\Compilation;

use App\Models\Compilation\ContextQuestion;
use App\Models\Geonames\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compilation\ContextQuestionDescription>
 */
class ContextQuestionDescriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'context_question_id' => ContextQuestion::factory(),
            'location_id' => Location::factory(),
            'description' => $this->faker->unique()->sentence(3, true),
        ];
    }
}
