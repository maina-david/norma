<?php

namespace App\Policies\Auth;

use App\Models\Auth\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * @codeCoverageIgnore
     *
     * @param User $me
     * @param User $user
     *
     * @return bool
     */
    public function manageInSettings(User $me, User $user): bool
    {
        if ($me->can('access.org.settings.all')) {
            return true;
        }

        foreach ($user->organisations as $org) {
            if ($me->isOrganisationAdmin($org)) {
                return true;
            }
        }

        return false;
    }
}
