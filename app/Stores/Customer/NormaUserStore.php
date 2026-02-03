<?php

namespace App\Stores\Customer;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Stores\Traits\AttachesDetaches;
use Exception;
use Illuminate\Support\Collection;

class NormaUserStore
{
    use AttachesDetaches;

    /**
     * Attach the users to the norma.
     *
     * @param Norma                $norma
     * @param Collection<int, User> $users
     *
     * @throws Exception
     *
     * @return Norma
     */
    public function attachAdminUsers(Norma $norma, Collection $users): Norma
    {
        $this->attachRelations($norma, 'users', $users, ['is_admin' => 1]);

        return $norma;
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
        $currentNormas = $user->normas()
            ->wherePivot('via_teams', true)
            ->pluck('id')
            ->all();

        $viaTeams = Norma::userHasAccessViaTeams($user)
            ->pluck('id')
            ->all();

        $detach = array_diff($currentNormas, $viaTeams);

        // use smaller chunks to reduce memory footprint
        collect($detach)->chunk(200)
            ->each(function ($ids) use ($user) {
                $this->detachRelations($user, 'normas', $ids);
            });

        collect($viaTeams)->chunk(200)
            ->each(function ($ids) use ($user) {
                $this->attachRelations($user, 'normas', $ids, ['via_teams' => true]);
            });
    }
}
