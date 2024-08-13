<?php

namespace App\Actions\Customer\Libryo;

use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use App\Stores\Customer\LibryoTeamStore;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class AddTeams
{
    use AsAction;

    /**
     * @param LibryoTeamStore $libryoTeamStore
     */
    public function __construct(protected LibryoTeamStore $libryoTeamStore)
    {
    }

    /**
     * @param Libryo            $libryo
     * @param array<int>        $teamIds
     * @param Organisation|null $organisation
     *
     * @return void
     */
    public function handle(Libryo $libryo, array $teamIds, ?Organisation $organisation): void
    {
        $query = is_null($organisation)
            ? (new Team())->newQuery()
            : Team::forOrganisation($organisation->id);

        /** @var Collection<Team> */
        $teams = $query->whereKey($teamIds)
            ->get(['id']);

        $this->libryoTeamStore->attachTeams($libryo, $teams);
    }
}
