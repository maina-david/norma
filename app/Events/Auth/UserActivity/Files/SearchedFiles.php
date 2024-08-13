<?php

namespace App\Events\Auth\UserActivity\Files;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\UserActivityEvent;

class SearchedFiles extends UserActivityEvent
{
    /**
     * {@inheritDoc}
     */
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::searchedFiles();
    }

    /**
     * {@inheritDoc}
     */
    public function toJson(int $options = 0): string|false
    {
        return '{}';
    }
}
