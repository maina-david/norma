<?php

namespace Database\Factories\Notify;

use App\Enums\Notifications\NotificationType;
use App\Models\Auth\User;
use App\Models\Model;
use App\Models\Notify\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => Str::uuid()->toString(),
            'type' => NotificationType::mention()->value,
            'notifiable_type' => User::class,
            'notifiable_id' => User::factory(),
            'data' => ['body' => ['type' => NotificationType::mention()->value]],
        ];
    }
}
