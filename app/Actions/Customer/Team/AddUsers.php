<?php

namespace App\Actions\Customer\Team;

use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use App\Stores\Customer\TeamUserStore;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class AddUsers
{
    use AsAction;

    /**
     * @param TeamUserStore $teamUserStore
     */
    public function __construct(protected TeamUserStore $teamUserStore)
    {
    }

    /**
     * @param Team              $team
     * @param array<int, int>   $userIds
     * @param Organisation|null $organisation
     *
     * @return void
     */
    public function handle(Team $team, array $userIds, ?Organisation $organisation): void
    {
        $query = is_null($organisation)
            ? (new User())->newQuery()
            : User::inOrganisation($organisation->id);

        /** @var Collection<User> */
        $users = $query->whereKey($userIds)
            ->whereDoesntHave('teams', function ($q) use ($team) {
                $q->whereKey($team->id);
            })
            ->get(['id']);

        $this->teamUserStore->attachUsers($team, $users);
    }
}
