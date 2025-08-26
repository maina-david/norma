<?php

namespace App\Http\Controllers\Assess\My\Traits;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use Illuminate\Database\Eloquent\Builder;

trait ComposesResponsesQuery
{
    protected function composeResponsesQuery(
        Builder $query,
        ?Norma $norma,
        ?Organisation $organisation,
        ?User $user,
    ): Builder {
        return $query->with([
            'assessmentItem' => fn ($q) => $q->withCount([
                'references' => function ($q) use ($norma, $organisation, $user) {
                    if ($norma) {
                        $q->forNorma($norma);
                    } else {
                        $q->forOrganisationUserAccess($organisation, $user);
                    }
                },
            ]),
            'assessmentItem.legalDomain',
            'assessmentItem.legalDomain.topParent',
            'norma' => fn ($q) => $q->userHasAccess($user),
            'lastAnsweredBy',
        ])
            ->withCount(['files', 'comments', 'tasks']);
    }
}
