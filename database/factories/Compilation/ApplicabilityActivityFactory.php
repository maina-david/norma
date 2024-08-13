<?php

namespace Database\Factories\Compilation;

use App\Enums\Compilation\ApplicabilityActivityType;
use App\Models\Auth\User;
use App\Models\Compilation\ApplicabilityActivity;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Libryo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicabilityActivityFactory extends Factory
{
    protected $model = ApplicabilityActivity::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'activity_type' => $this->faker->randomElement(ApplicabilityActivityType::cases())->value,
            'previous' => null,
            'current' => 1,
            'place_id' => Libryo::factory(),
            'user_id' => User::factory(),
            'context_question_id' => ContextQuestion::factory(),
        ];
    }
}
