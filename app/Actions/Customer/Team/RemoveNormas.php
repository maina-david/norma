<?php

namespace App\Actions\Customer\Team;

use App\Models\Customer\Norma;
use App\Models\Customer\Team;
use App\Stores\Customer\NormaTeamStore;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveNormas
{
    use AsAction;

    /**
     * @param NormaTeamStore $normaTeamStore
     */
    public function __construct(protected NormaTeamStore $normaTeamStore)
    {
    }

    /**
     * @param Team               $team
     * @param Collection<Norma> $normas
     *
     * @return void
     */
    public function handle(Team $team, Collection $normas): void
    {
        $this->normaTeamStore->detachNormas($team, $normas);
    }
}
