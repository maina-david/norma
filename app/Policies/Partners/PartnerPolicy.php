<?php

namespace App\Policies\Partners;

use App\Models\Auth\User;
use App\Models\Partners\Partner;
use Illuminate\Auth\Access\HandlesAuthorization;

class PartnerPolicy
{
    use HandlesAuthorization;

    /**
     * Check if the user can create a partner organisation.
     *
     * @param User    $user
     * @param Partner $partner
     *
     * @return bool
     */
    public function createOrganisation(User $user, Partner $partner): bool
    {
        return $user->hasPartnerAccess($partner) && $user->isIntegrationUser();
    }
}
