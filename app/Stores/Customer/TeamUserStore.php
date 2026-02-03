<?php

namespace App\Stores\Customer;

use App\Actions\Auth\User\SyncNormaUserCache;
use App\Models\Auth\User;
use App\Models\Customer\Team;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Database\Eloquent\Collection;

class TeamUserStore
{
    use AttachesDetaches;

    /**
     * @param User                 $user
     * @param Collection<Team|int> $teams
     *
     * @return void
     */
    public function attachTeams(User $user, Collection $teams): void
    {
        $this->attachRelations($user, 'teams', $teams);
        SyncNormaUserCache::dispatch($user->id);
    }

    /**
     * @param User                 $user
     * @param Collection<Team|int> $teams
     *
     * @return void
     */
    public function detachTeams(User $user, Collection $teams): void
    {
        $this->detachRelations($user, 'teams', $teams);
        SyncNormaUserCache::dispatch($user->id);
    }

    /**
     * @param Team                 $team
     * @param Collection<User|int> $users
     *
     * @return void
     */
    public function attachUsers(Team $team, Collection $users): void
    {
        $this->attachRelations($team, 'users', $users, [], fn ($u) => SyncNormaUserCache::dispatch($u->id ?? $u));
    }

    /**
     * @param Team                 $team
     * @param Collection<User|int> $users
     *
     * @return void
     */
    public function detachUsers(Team $team, Collection $users): void
    {
        $this->detachRelations($team, 'users', $users, fn ($u) => SyncNormaUserCache::dispatch($u->id ?? $u));
    }
}
