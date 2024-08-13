<?php

namespace Database\Factories\Auth;

use App\Enums\Auth\UserActivityType;
use App\Models\Auth\User;
use App\Models\Auth\UserActivity;
use App\Models\Customer\Libryo;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserActivityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserActivity::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'place_id' => Libryo::factory(),
            'activity_type' => $this->faker->randomElement(UserActivityType::options()),
        ];
    }

    /**
     * Indicate that the user is suspended.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function typeLibryoActivate(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'activity_type' => UserActivityType::libryoActivate(),
            ];
        });
    }
}
