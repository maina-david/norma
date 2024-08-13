<?php

namespace Database\Factories\Auth;

use App\Models\Auth\User;
use App\Models\Auth\UserTrial;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserTrialFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserTrial::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'expires_at' => $this->faker->dateTimeThisMonth(now()->addMonth()),
        ];
    }
}
