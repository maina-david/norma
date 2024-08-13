<?php

namespace App\Events\Auth\UserActivity\LegalUpdates;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\UserActivityEvent;

class LegalUpdatesSearched extends UserActivityEvent
{
    /**
     * {@inheritDoc}
     */
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::searchedLegalUpdate();
    }

    /**
     * {@inheritDoc}
     */
    public function toJson(int $options = 0): string|false
    {
        return '{}';
    }
}
