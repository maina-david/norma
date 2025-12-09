<?php

namespace App\Policies\Customer;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use Illuminate\Auth\Access\HandlesAuthorization;

class NormaPolicy
{
    use HandlesAuthorization;

    /**
     * @param User   $user
     * @param Norma $norma
     *
     * @return bool
     */
    public function view(User $user, Norma $norma): bool
    {
        return !(!$user->hasNormaAccess($norma));
    }

    /**
     * @codeCoverageIgnore
     *
     * @param User   $user
     * @param Norma $norma
     *
     * @return bool
     */
    public function manageInSettings(User $user, Norma $norma): bool
    {
        if ($user->can('access.org.settings.all')) {
            return true;
        }

        if (!$norma->organisation) {
            return false;
        }

        return (bool) $user->isOrganisationAdmin($norma->organisation);
    }
}
