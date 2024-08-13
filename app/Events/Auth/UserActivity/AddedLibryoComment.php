<?php

namespace App\Events\Auth\UserActivity;

use App\Enums\Auth\UserActivityType;

class AddedLibryoComment extends AbstractAddedComment
{
    /**
     * @return UserActivityType
     **/
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::libryoComment();
    }
}
