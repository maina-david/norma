<?php

namespace App\Stores\Customer;

use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Stores\Traits\AttachesDetaches;
use Exception;
use Illuminate\Support\Collection;

class LibryoUserStore
{
    use AttachesDetaches;

    /**
     * Attach the users to the libryo.
     *
     * @param Libryo                $libryo
     * @param Collection<int, User> $users
     *
     * @throws Exception
     *
     * @return Libryo
     */
    public function attachAdminUsers(Libryo $libryo, Collection $users): Libryo
    {
        $this->attachRelations($libryo, 'users', $users, ['is_admin' => 1]);

        return $libryo;
    }

    /**
     * @param User $user
     *
     * @throws Exception
     *
     * @return void
     */
    public function syncFromTeams(User $user): void
    {
        $currentLibryos = $user->libryos()
            ->wherePivot('via_teams', true)
            ->pluck('id')
            ->all();

        $viaTeams = Libryo::userHasAccessViaTeams($user)
            ->pluck('id')
            ->all();

        $detach = array_diff($currentLibryos, $viaTeams);

        // use smaller chunks to reduce memory footprint
        collect($detach)->chunk(200)
            ->each(function ($ids) use ($user) {
                $this->detachRelations($user, 'libryos', $ids);
            });

        collect($viaTeams)->chunk(200)
            ->each(function ($ids) use ($user) {
                $this->attachRelations($user, 'libryos', $ids, ['via_teams' => true]);
            });
    }
}
