<?php

namespace App\Events\Auth\UserActivity;

use App\Enums\Auth\UserActivityType;

class AddedLegalUpdateComment extends AbstractAddedComment
{
    /**
     * @return UserActivityType
     **/
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::legalUpdateComment();
    }
}
