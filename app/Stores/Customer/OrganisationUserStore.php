<?php

namespace App\Stores\Customer;

use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Database\Eloquent\Collection;

class OrganisationUserStore
{
    use AttachesDetaches;

    /**
     * @param Organisation         $organisation
     * @param Collection<User|int> $users
     *
     * @return void
     */
    public function attachUsers(Organisation $organisation, Collection $users): void
    {
        $this->attachRelations($organisation, 'users', $users);
    }

    /**
     * @param Organisation         $organisation
     * @param Collection<User|int> $users
     *
     * @return void
     */
    public function detachUsers(Organisation $organisation, Collection $users): void
    {
        $this->detachRelations($organisation, 'users', $users);
    }

    /**
     * @param User                         $user
     * @param Collection<Organisation|int> $organisations
     *
     * @return void
     */
    public function attachOrganisations(User $user, Collection $organisations): void
    {
        $this->attachRelations($user, 'organisations', $organisations);
    }

    /**
     * @param User                         $user
     * @param Collection<Organisation|int> $organisations
     *
     * @return void
     */
    public function detachOrganisations(User $user, Collection $organisations): void
    {
        $this->detachRelations($user, 'organisations', $organisations);
    }
}
