<?php

namespace App\Policies\Assess;

use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssessmentItemResponsePolicy
{
    use HandlesAuthorization;

    /**
     * @param User                   $user
     * @param AssessmentItemResponse $aiResponse
     *
     * @return bool
     */
    public function view(User $user, AssessmentItemResponse $aiResponse): bool
    {
        return $aiResponse->libryo ? $user->hasLibryoAccess($aiResponse->libryo) : false;
    }

    /**
     * @param User                   $user
     * @param AssessmentItemResponse $aiResponse
     *
     * @return bool
     */
    public function answer(User $user, AssessmentItemResponse $aiResponse): bool
    {
        return $this->view($user, $aiResponse);
    }
}
