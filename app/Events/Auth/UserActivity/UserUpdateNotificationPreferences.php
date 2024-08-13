<?php

namespace App\Events\Auth\UserActivity;

use App\Enums\Auth\UserActivityType;
use App\Models\Auth\User;

class UserUpdateNotificationPreferences extends UserActivityEvent
{
    /**
     * @param User                 $user
     * @param array<string, mixed> $preferences
     */
    public function __construct(
        User $user,
        protected array $preferences,
    ) {
        parent::__construct($user);
    }

    /**
     * @param int $options
     *
     * @return string|false
     */
    public function toJson($options = 0): string|false
    {
        return json_encode([
            'email_daily' => $this->preferences['email_daily'],
            'email_monthly' => $this->preferences['email_monthly'],
        ], $options);
    }

    /**
     * @return UserActivityType
     **/
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::updateNotificationPreferences();
    }
}
