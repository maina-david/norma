<?php

namespace App\Stores\Customer;

use App\Actions\Customer\Team\SyncNormaUserCacheForTeam;
use App\Models\Customer\Norma;
use App\Models\Customer\Team;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Database\Eloquent\Collection;

class NormaTeamStore
{
    use AttachesDetaches;

    /**
     * @param Norma           $norma
     * @param Collection<Team> $teams
     *
     * @return void
     */
    public function attachTeams(Norma $norma, Collection $teams): void
    {
        $this->attachRelations($norma, 'teams', $teams, [], fn ($t) => SyncNormaUserCacheForTeam::dispatch($t->id));
    }

    /**
     * @param Norma           $norma
     * @param Collection<Team> $teams
     *
     * @return void
     */
    public function detachTeams(Norma $norma, Collection $teams): void
    {
        $this->detachRelations($norma, 'teams', $teams, fn ($t) => SyncNormaUserCacheForTeam::dispatch($t->id));
    }

    /**
     * @param Team                   $team
     * @param Collection<Norma|int> $normas
     *
     * @return void
     */
    public function attachNormas(Team $team, Collection $normas): void
    {
        $this->attachRelations($team, 'normas', $normas);
        SyncNormaUserCacheForTeam::dispatch($team->id);
    }

    /**
     * @param Team                   $team
     * @param Collection<Norma|int> $normas
     *
     * @return void
     */
    public function detachNormas(Team $team, Collection $normas): void
    {
        $this->detachRelations($team, 'normas', $normas);
        SyncNormaUserCacheForTeam::dispatch($team->id);
    }
}
