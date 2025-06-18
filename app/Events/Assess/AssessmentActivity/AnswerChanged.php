<?php

namespace App\Events\Assess\AssessmentActivity;

use App\Enums\Assess\AssessActivityType;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Customer\Norma;

class AnswerChanged extends AssessmentActivityEvent
{
    /**
     * @param User                   $user
     * @param Norma                 $norma
     * @param AssessmentItemResponse $response
     * @param int|null               $fromAnswer
     * @param int|null               $toAnswer
     * @param string                 $notes
     */
    public function __construct(
        User $user,
        Norma $norma,
        AssessmentItemResponse $response,
        public ?int $fromAnswer,
        public ?int $toAnswer,
        public string $notes
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
        return AssessActivityType::answerChange();
    }

    /**
     * Get previous answer for the activity.
     *
     * @return int
     */
    public function getFromAnswer(): ?int
    {
        return $this->fromAnswer;
    }

    /**
     * Get current answer for the activity.
     *
     * @return int
     */
    public function getToAnswer(): ?int
    {
        return $this->toAnswer;
    }

    /**
     * Get the notes for the activity.
     *
     * @return string
     */
    public function getActivityNotes(): string
    {
        return $this->notes;
    }
}
