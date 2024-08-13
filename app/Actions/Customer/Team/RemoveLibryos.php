<?php

namespace App\Actions\Customer\Team;

use App\Models\Customer\Libryo;
use App\Models\Customer\Team;
use App\Stores\Customer\LibryoTeamStore;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveLibryos
{
    use AsAction;

    /**
     * @param LibryoTeamStore $libryoTeamStore
     */
    public function __construct(protected LibryoTeamStore $libryoTeamStore)
    {
    }

    /**
     * @param Team               $team
     * @param Collection<Libryo> $libryos
     *
     * @return void
     */
    public function handle(Team $team, Collection $libryos): void
    {
        $this->libryoTeamStore->detachLibryos($team, $libryos);
    }
}
