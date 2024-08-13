<?php

namespace App\Actions\Customer\Libryo;

use App\Models\Customer\Libryo;
use App\Models\Customer\Team;
use App\Stores\Customer\LibryoTeamStore;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveTeams
{
    use AsAction;

    /**
     * @param LibryoTeamStore $libryoTeamStore
     */
    public function __construct(protected LibryoTeamStore $libryoTeamStore)
    {
    }

    /**
     * @param Libryo           $libryo
     * @param Collection<Team> $teams
     *
     * @return void
     */
    public function handle(Libryo $libryo, Collection $teams): void
    {
        $this->libryoTeamStore->detachTeams($libryo, $teams);
    }
}
