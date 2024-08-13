<?php

namespace Database\Factories\Notify;

use App\Models\Notify\NotificationStatusReason;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationStatusReason>
 */
class NotificationStatusReasonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'text' => $this->faker->paragraph(),
        ];
    }
}
