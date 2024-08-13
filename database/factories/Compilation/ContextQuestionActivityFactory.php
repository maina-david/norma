<?php

namespace Database\Factories\Compilation;

use App\Enums\Compilation\ContextQuestionActivityType;
use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Libryo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compilation\ContextQuestionActivity>
 */
class ContextQuestionActivityFactory extends Factory
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
            'place_id' => Libryo::factory(),
            'user_id' => User::factory(),
            'activity_type' => ContextQuestionActivityType::ANSWER_CHANGED,
            'from_status' => $this->faker->randomElement(array_keys(ContextQuestionAnswer::lang())),
            'to_status' => $this->faker->randomElement(array_keys(ContextQuestionAnswer::lang())),
        ];
    }
}
