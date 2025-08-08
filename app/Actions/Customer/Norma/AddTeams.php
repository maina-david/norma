<?php

namespace App\Actions\Customer\Norma;

use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use App\Stores\Customer\NormaTeamStore;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class AddTeams
{
    use AsAction;

    /**
     * @param NormaTeamStore $normaTeamStore
     */
    public function __construct(protected NormaTeamStore $normaTeamStore)
    {
    }

    /**
     * @param Norma            $norma
     * @param array<int>        $teamIds
     * @param Organisation|null $organisation
     *
     * @return void
     */
    public function handle(Norma $norma, array $teamIds, ?Organisation $organisation): void
    {
        $query = is_null($organisation)
            ? (new Team())->newQuery()
            : Team::forOrganisation($organisation->id);

        /** @var Collection<Team> */
        $teams = $query->whereKey($teamIds)
            ->get(['id']);

        $this->normaTeamStore->attachTeams($norma, $teams);
    }
}
