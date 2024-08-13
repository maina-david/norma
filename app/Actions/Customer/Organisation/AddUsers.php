<?php

namespace App\Actions\Customer\Organisation;

use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Stores\Customer\OrganisationUserStore;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class AddUsers
{
    use AsAction;

    /**
     * @param OrganisationUserStore $organisationUserStore
     */
    public function __construct(protected OrganisationUserStore $organisationUserStore)
    {
    }

    /**
     * @param Organisation    $organisation
     * @param array<int, int> $userIds
     *
     * @return void
     */
    public function handle(Organisation $organisation, array $userIds): void
    {
        /** @var Collection<User> */
        $users = User::whereKey($userIds)
            ->whereDoesntHave('organisations', function ($q) use ($organisation) {
                $q->whereKey($organisation->id);
            })
            ->get(['id']);

        $this->organisationUserStore->attachUsers($organisation, $users);
    }
}
