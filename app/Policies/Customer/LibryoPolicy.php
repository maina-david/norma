<?php

namespace App\Policies\Customer;

use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use Illuminate\Auth\Access\HandlesAuthorization;

class LibryoPolicy
{
    use HandlesAuthorization;

    /**
     * @param User   $user
     * @param Libryo $libryo
     *
     * @return bool
     */
    public function view(User $user, Libryo $libryo): bool
    {
        return !(!$user->hasLibryoAccess($libryo));
    }

    /**
     * @codeCoverageIgnore
     *
     * @param User   $user
     * @param Libryo $libryo
     *
     * @return bool
     */
    public function manageInSettings(User $user, Libryo $libryo): bool
    {
        if ($user->can('access.org.settings.all')) {
            return true;
        }

        if (!$libryo->organisation) {
            return false;
        }

        return (bool) $user->isOrganisationAdmin($libryo->organisation);
    }
}
