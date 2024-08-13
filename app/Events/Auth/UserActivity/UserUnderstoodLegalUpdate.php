<?php

namespace App\Events\Auth\UserActivity;

use App\Enums\Auth\UserActivityType;
use App\Models\Auth\User;
use App\Models\Notify\LegalUpdate;

class UserUnderstoodLegalUpdate extends UserActivityEvent
{
    /**
     * @param User        $user
     * @param LegalUpdate $notification
     */
    public function __construct(
        User $user,
        protected LegalUpdate $notification
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
            'register_notification_id' => $this->notification->id,
        ], $options);
    }

    /**
     * @return UserActivityType
     **/
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::legalUpdateUnderstood();
    }
}
