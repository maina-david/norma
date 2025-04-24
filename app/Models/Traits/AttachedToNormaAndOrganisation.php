<?php

namespace App\Models\Traits;

use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use Illuminate\Database\Eloquent\Builder;

trait AttachedToNormaAndOrganisation
{
    /**
     * Scope for Norma filter.
     *
     * @param Builder $builder
     * @param Norma  $norma
     *
     * @return Builder
     */
    abstract public function scopeForNorma(Builder $builder, Norma $norma): Builder;

    /**
     * Scope for organisation filter.
     *
     * @param Builder      $builder
     * @param Organisation $organisation
     *
     * @return Builder
     */
    abstract public function scopeForOrganisation(Builder $builder, Organisation $organisation): Builder;

    /**
     * Filter by Norma if present else, use the organisation.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \App\Models\Customer\Norma|null      $norma
     * @param \App\Models\Customer\Organisation     $organisation
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForNormaOrOrganisation(Builder $builder, ?Norma $norma, Organisation $organisation): Builder
    {
        // @phpstan-ignore-next-line
        return $builder->when($norma, fn ($query) => $query->forNorma($norma))
            ->when(!$norma, fn ($query) => $query->forOrganisation($organisation));
    }
}
