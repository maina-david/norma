<?php

namespace App\Events\Auth\UserActivity\AssessmentItems;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\UserActivityEvent;
use App\Models\Assess\AssessmentItem;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;

class CreatedAssessmentItem extends UserActivityEvent
{
    public function __construct(protected AssessmentItem $item, User $user, ?Libryo $libryo = null, ?Organisation $organisation = null)
    {
        parent::__construct($user, $libryo, $organisation);
    }

    /**
     * {@inheritDoc}
     */
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::createdAssessmentItem();
    }

    /**
     * {@inheritDoc}
     */
    public function toJson(int $options = 0): string|false
    {
        return json_encode([
            'assessment_item' => [
                'id' => $this->item->id,
            ],
        ], $options);
    }
}
