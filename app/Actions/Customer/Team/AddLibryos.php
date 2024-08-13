<?php

namespace App\Actions\Customer\Team;

use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use App\Stores\Customer\LibryoTeamStore;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class AddLibryos
{
    use AsAction;

    /**
     * @param LibryoTeamStore $libryoTeamStore
     */
    public function __construct(protected LibryoTeamStore $libryoTeamStore)
    {
    }

    /**
     * @param Team              $team
     * @param array<int>        $libryoIds
     * @param Organisation|null $organisation
     *
     * @return void
     */
    public function handle(Team $team, array $libryoIds, ?Organisation $organisation)
    {
        $query = is_null($organisation)
            ? (new Libryo())->newQuery()
            : Libryo::forOrganisation($organisation->id);

        /** @var Collection<Libryo> */
        $libryos = $query->whereKey($libryoIds)
            ->get(['id']);

        $this->libryoTeamStore->attachLibryos($team, $libryos);
    }
}
