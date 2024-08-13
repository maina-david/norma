<?php

namespace App\Stores\Customer;

use App\Actions\Customer\Team\SyncLibryoUserCacheForTeam;
use App\Models\Customer\Libryo;
use App\Models\Customer\Team;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Database\Eloquent\Collection;

class LibryoTeamStore
{
    use AttachesDetaches;

    /**
     * @param Libryo           $libryo
     * @param Collection<Team> $teams
     *
     * @return void
     */
    public function attachTeams(Libryo $libryo, Collection $teams): void
    {
        $this->attachRelations($libryo, 'teams', $teams, [], fn ($t) => SyncLibryoUserCacheForTeam::dispatch($t->id));
    }

    /**
     * @param Libryo           $libryo
     * @param Collection<Team> $teams
     *
     * @return void
     */
    public function detachTeams(Libryo $libryo, Collection $teams): void
    {
        $this->detachRelations($libryo, 'teams', $teams, fn ($t) => SyncLibryoUserCacheForTeam::dispatch($t->id));
    }

    /**
     * @param Team                   $team
     * @param Collection<Libryo|int> $libryos
     *
     * @return void
     */
    public function attachLibryos(Team $team, Collection $libryos): void
    {
        $this->attachRelations($team, 'libryos', $libryos);
        SyncLibryoUserCacheForTeam::dispatch($team->id);
    }

    /**
     * @param Team                   $team
     * @param Collection<Libryo|int> $libryos
     *
     * @return void
     */
    public function detachLibryos(Team $team, Collection $libryos): void
    {
        $this->detachRelations($team, 'libryos', $libryos);
        SyncLibryoUserCacheForTeam::dispatch($team->id);
    }
}
