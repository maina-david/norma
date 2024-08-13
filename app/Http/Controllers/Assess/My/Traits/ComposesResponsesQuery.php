<?php

namespace App\Http\Controllers\Assess\My\Traits;

use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use Illuminate\Database\Eloquent\Builder;

trait ComposesResponsesQuery
{
    protected function composeResponsesQuery(
        Builder $query,
        ?Libryo $libryo,
        ?Organisation $organisation,
        ?User $user,
    ): Builder {
        return $query->with([
            'assessmentItem' => fn ($q) => $q->withCount([
                'references' => function ($q) use ($libryo, $organisation, $user) {
                    if ($libryo) {
                        $q->forLibryo($libryo);
                    } else {
                        $q->forOrganisationUserAccess($organisation, $user);
                    }
                },
            ]),
            'assessmentItem.legalDomain',
            'assessmentItem.legalDomain.topParent',
            'libryo' => fn ($q) => $q->userHasAccess($user),
            'lastAnsweredBy',
        ])
            ->withCount(['files', 'comments', 'tasks']);
    }
}
