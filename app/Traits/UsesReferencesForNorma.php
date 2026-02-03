<?php

namespace App\Traits;

use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\NormaReference;
use Illuminate\Database\Eloquent\Builder;

trait UsesReferencesForNorma
{
    /**
     * Get attached references subquery.
     *
     * @param \App\Models\Customer\Norma|null  $norma
     * @param \App\Models\Customer\Organisation $organisation
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getReferenceSubQuery(?Norma $norma, Organisation $organisation): Builder
    {
        $orgSubQuery = Norma::select(['id'])->where('organisation_id', $organisation->id);

        return NormaReference::when($norma, fn ($query) => $query->where('place_id', $norma?->id))
            ->when(!$norma, fn ($query) => $query->whereIn('place_id', $orgSubQuery))
            ->select(['reference_id']);
    }
}
