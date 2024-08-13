<?php

namespace App\Models\Traits;

use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use Illuminate\Database\Eloquent\Builder;

trait AttachedToLibryoAndOrganisation
{
    /**
     * Scope for Libryo filter.
     *
     * @param Builder $builder
     * @param Libryo  $libryo
     *
     * @return Builder
     */
    abstract public function scopeForLibryo(Builder $builder, Libryo $libryo): Builder;

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
     * Filter by Libryo if present else, use the organisation.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \App\Models\Customer\Libryo|null      $libryo
     * @param \App\Models\Customer\Organisation     $organisation
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForLibryoOrOrganisation(Builder $builder, ?Libryo $libryo, Organisation $organisation): Builder
    {
        // @phpstan-ignore-next-line
        return $builder->when($libryo, fn ($query) => $query->forLibryo($libryo))
            ->when(!$libryo, fn ($query) => $query->forOrganisation($organisation));
    }
}
