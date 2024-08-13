<?php

namespace App\Events\Auth\UserActivity;

use App\Enums\Auth\UserActivityType;

class AddedTaskComment extends AbstractAddedComment
{
    /**
     * @return UserActivityType
     **/
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::taskComment();
    }
}
