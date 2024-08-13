<?php

namespace App\Events\Auth\UserActivity\AssessmentItems;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\UserActivityEvent;

class BulkAnsweredAssessmentItems extends UserActivityEvent
{
    /**
     * {@inheritDoc}
     */
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::bulkAnsweredAssessmentItems();
    }

    /**
     * {@inheritDoc}
     */
    public function toJson(int $options = 0): string|false
    {
        return '{}';
    }
}
