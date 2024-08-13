<?php

namespace App\Actions\Customer\Team;

use App\Models\Auth\User;
use App\Models\Customer\Team;
use App\Stores\Customer\TeamUserStore;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveUsers
{
    use AsAction;

    /**
     * @param TeamUserStore $teamUserStore
     */
    public function __construct(protected TeamUserStore $teamUserStore)
    {
    }

    /**
     * @param Team             $team
     * @param Collection<User> $users
     *
     * @return void
     */
    public function handle(Team $team, Collection $users): void
    {
        $this->teamUserStore->detachUsers($team, $users);
    }
}
