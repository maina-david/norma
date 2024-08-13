<?php

namespace App\Http\ResourceActions\Ontology;

use App\Contracts\Http\ResourceAction;
use App\Http\ResourceActions\RestoresArchivedResources;
use App\Models\Auth\User;
use App\Models\Ontology\LegalDomain;
use Illuminate\Database\Eloquent\Builder;

class RestoreLegalDomain implements ResourceAction
{
    use RestoresArchivedResources;

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(): Builder
    {
        return (new LegalDomain())->newQuery();
    }

    /**
     * Check if the user is authorised to perform the action.
     *
     * @param User|null $user
     *
     * @return bool
     */
    public function authorise(?User $user): bool
    {
        return $user && $user->can('collaborate.ontology.legal-domain.archive');
    }
}
