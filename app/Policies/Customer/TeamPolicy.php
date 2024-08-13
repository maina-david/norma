<?php

namespace App\Policies\Customer;

use App\Models\Auth\User;
use App\Models\Customer\Team;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * @codeCoverageIgnore
     *
     * @param User $user
     * @param Team $team
     *
     * @return bool
     */
    public function manageInSettings(User $user, Team $team): bool
    {
        if ($user->can('access.org.settings.all')) {
            return true;
        }

        if (!$team->organisation) {
            return false;
        }

        return (bool) $user->isOrganisationAdmin($team->organisation);
    }
}
