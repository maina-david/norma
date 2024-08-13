<?php

namespace App\Events\Assess\AssessmentActivity;

use App\Enums\Assess\AssessActivityType;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;

class RequirementUnlinked extends AssessmentActivityEvent
{
    /**
     * @param User                   $user
     * @param Libryo                 $libryo
     * @param AssessmentItemResponse $response
     * @param int                    $referenceId
     */
    public function __construct(
        User $user,
        Libryo $libryo,
        AssessmentItemResponse $response,
        protected int $referenceId,
    ) {
        parent::__construct($user, $response, $libryo);
    }

    /**
     * Get activity type for the activity.
     *
     * @return AssessActivityType
     */
    public function getActivityType(): AssessActivityType
    {
        return AssessActivityType::requirementUnlinked();
    }

    /**
     * Get the reference that has been linked.
     *
     * @return int
     */
    public function getReferenceId(): int
    {
        return $this->referenceId;
    }
}
