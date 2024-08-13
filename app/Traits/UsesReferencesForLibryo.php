<?php

namespace App\Traits;

use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\LibryoReference;
use Illuminate\Database\Eloquent\Builder;

trait UsesReferencesForLibryo
{
    /**
     * Get attached references subquery.
     *
     * @param \App\Models\Customer\Libryo|null  $libryo
     * @param \App\Models\Customer\Organisation $organisation
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getReferenceSubQuery(?Libryo $libryo, Organisation $organisation): Builder
    {
        $orgSubQuery = Libryo::select(['id'])->where('organisation_id', $organisation->id);

        return LibryoReference::when($libryo, fn ($query) => $query->where('place_id', $libryo?->id))
            ->when(!$libryo, fn ($query) => $query->whereIn('place_id', $orgSubQuery))
            ->select(['reference_id']);
    }
}
