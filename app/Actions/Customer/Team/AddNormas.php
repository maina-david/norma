<?php

namespace App\Actions\Customer\Team;

use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use App\Stores\Customer\NormaTeamStore;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class AddNormas
{
    use AsAction;

    /**
     * @param NormaTeamStore $normaTeamStore
     */
    public function __construct(protected NormaTeamStore $normaTeamStore)
    {
    }

    /**
     * @param Team              $team
     * @param array<int>        $normaIds
     * @param Organisation|null $organisation
     *
     * @return void
     */
    public function handle(Team $team, array $normaIds, ?Organisation $organisation)
    {
        $query = is_null($organisation)
            ? (new Norma())->newQuery()
            : Norma::forOrganisation($organisation->id);

        /** @var Collection<Norma> */
        $normas = $query->whereKey($normaIds)
            ->get(['id']);

        $this->normaTeamStore->attachNormas($team, $normas);
    }
}
