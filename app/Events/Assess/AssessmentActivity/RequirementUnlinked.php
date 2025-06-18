<?php

namespace App\Events\Assess\AssessmentActivity;

use App\Enums\Assess\AssessActivityType;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Customer\Norma;

class RequirementUnlinked extends AssessmentActivityEvent
{
    /**
     * @param User                   $user
     * @param Norma                 $norma
     * @param AssessmentItemResponse $response
     * @param int                    $referenceId
     */
    public function __construct(
        User $user,
        Norma $norma,
        AssessmentItemResponse $response,
        protected int $referenceId,
    ) {
        parent::__construct($user, $response, $norma);
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
