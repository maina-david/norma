<?php

namespace Database\Factories\Log;

use App\Enums\Auth\LifecycleStage;
use App\Models\Auth\User;
use App\Models\Log\UserLifecycleActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserLifecycleActivityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserLifecycleActivity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'from_lifecycle' => LifecycleStage::notOnboarded()->value,
            'to_lifecycle' => LifecycleStage::active()->value,
        ];
    }
}
