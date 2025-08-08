<?php

namespace App\Actions\Customer\Norma;

use App\Models\Customer\Norma;
use App\Models\Customer\Team;
use App\Stores\Customer\NormaTeamStore;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveTeams
{
    use AsAction;

    /**
     * @param NormaTeamStore $normaTeamStore
     */
    public function __construct(protected NormaTeamStore $normaTeamStore)
    {
    }

    /**
     * @param Norma           $norma
     * @param Collection<Team> $teams
     *
     * @return void
     */
    public function handle(Norma $norma, Collection $teams): void
    {
        $this->normaTeamStore->detachTeams($norma, $teams);
    }
}
