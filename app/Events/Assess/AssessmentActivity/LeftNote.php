<?php

namespace App\Events\Assess\AssessmentActivity;

use App\Enums\Assess\AssessActivityType;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Comments\Comment;

class LeftNote extends AssessmentActivityEvent
{
    public function __construct(
        User $user,
        AssessmentItemResponse $response,
        public Comment $comment
    ) {
        parent::__construct($user, $response, $comment->norma, $comment->organisation);
    }

    public function getActivityType(): AssessActivityType
    {
        return AssessActivityType::comment();
    }

    public function getNote(): Comment
    {
        return $this->comment;
    }
}
