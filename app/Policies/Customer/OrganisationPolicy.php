<?php

namespace App\Policies\Customer;

use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganisationPolicy
{
    use HandlesAuthorization;

    /**
     * @param User         $user
     * @param Organisation $organisation
     *
     * @return bool
     */
    public function view(User $user, Organisation $organisation): bool
    {
        return !(!$user->isInOrganisation($organisation));
    }

    /**
     * Determine whether the user can administer the Organisation.
     *
     * @param User             $user
     * @param Organisation|int $organisation
     *
     * @return bool
     */
    public function administerOrganisation(User $user, Organisation|int $organisation): bool
    {
        if ($user->isAdmin()) {
            // @codeCoverageIgnoreStart
            return true;
            // @codeCoverageIgnoreEnd
        }

        if (!$organisation instanceof Organisation) {
            // @codeCoverageIgnoreStart
            /** @var Organisation $organisation */
            $organisation = Organisation::findOrFail($organisation);
            // @codeCoverageIgnoreEnd
        }

        return $user->isOrganisationAdmin($organisation);
    }
}
