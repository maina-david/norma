<?php

namespace Database\Factories\Notify;

use App\Models\Auth\User;
use App\Models\Notify\Reminder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ReminderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reminder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->unique()->words(3, true),
            'author_id' => User::factory(),
            'remind_on' => Carbon::now()->addDays(3),
        ];
    }
}
