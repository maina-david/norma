<?php

namespace App\Models\Traits;

use App\Models\Ontology\LegalDomain;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

trait LimitsLegalDomains
{
    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param Builder                 $query
     * @param Collection<LegalDomain> $domains
     *
     * @return Builder
     */
    public function scopeLimitedLegalDomains(Builder $query, Collection $domains): Builder
    {
        return
            $query->where(function ($q) use ($domains) {
                $q->doesntHave('legalDomains');
                $q->when($domains->isNotEmpty(), function ($q) use ($domains) {
                    $q->orWhereHas('legalDomains', function ($q) use ($domains) {
                        $ids = $domains->modelKeys();
                        $q->whereKey($ids);
                    });
                });
            });
    }
}
