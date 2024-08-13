<?php

namespace Database\Factories\System;

use App\Models\System\SystemNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

class SystemNotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SystemNotification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(3, true),
            'type' => rand(1, 2),
            'content' => $this->faker->paragraph(1),
            'expiry_date' => $this->faker->date(),
            'is_permanent' => false,
            'active' => true,
            'has_user_action' => true,
            'user_action_text' => $this->faker->words(3, true),
            'user_action_link' => $this->faker->url(),
        ];
    }
}
