<?php

namespace App\Models\Geonames;

use App\Models\AbstractModel;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperLocationType
 */
class LocationType extends AbstractModel
{
    protected $table = 'corpus_location_types';

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return HasMany
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'location_type_id');
    }

    /**
     * @return HasOne
     */
    public function oldestLocation(): HasOne
    {
        return $this->hasOne(Location::class, 'location_type_id')->oldestOfMany();
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\Customer\Norma           $norma
     * @param array<string, mixed>                  $referenceFilters
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForNormaViaUpdates(Builder $query, Norma $norma, array $referenceFilters = []): Builder
    {
        return $query->whereHas('locations.references', function ($builder) use ($norma, $referenceFilters) {
            $builder->whereIn('id', $norma->legalUpdates()->select('notify_reference_id'));
            if (!empty($referenceFilters)) {
                $builder->filter($referenceFilters);
            }
        });
    }

    /**
     * @param Builder              $query
     * @param Organisation         $organisation
     * @param User                 $user
     * @param array<string, mixed> $referenceFilters
     *
     * @return Builder
     */
    public function scopeForOrganisationUserAccessViaUpdates(Builder $query, Organisation $organisation, User $user, array $referenceFilters = []): Builder
    {
        LegalUpdate::whereRelation('normas', 'organisation_id', $organisation->id)->select('notify_reference_id');

        return $query->whereHas('locations.references', function ($builder) use ($user, $organisation, $referenceFilters) {
            $subQuery = LegalUpdate::select('notify_reference_id')
                ->whereHas('normas', function ($query) use ($user, $organisation) {
                    $query->where('organisation_id', $organisation->id)->userHasAccess($user);
                });

            $builder->whereIn('id', $subQuery);
            if (!empty($referenceFilters)) {
                $builder->filter($referenceFilters);
            }
        });
    }

    /**
     * @param Builder              $query
     * @param Norma               $norma
     * @param array<string, mixed> $referenceFilters
     *
     * @return Builder
     */
    public function scopeForNorma(Builder $query, Norma $norma, array $referenceFilters = []): Builder
    {
        return $query->whereHas('locations.references', function ($q) use ($norma, $referenceFilters) {
            $q->forNorma($norma);
            if (!empty($referenceFilters)) {
                $q->filter($referenceFilters);
            }
        });
    }

    /**
     * @param Builder              $query
     * @param Norma               $norma
     * @param array<string, mixed> $referenceFilters
     *
     * @return Builder
     */
    public function scopeForNormaWithLocations(Builder $query, Norma $norma, array $referenceFilters = []): Builder
    {
        return $query->forNorma($norma, $referenceFilters)
            ->with([
                'locations' => function ($q) use ($norma) {
                    $q->whereHas(
                        'references',
                        function ($q) use ($norma) {
                            $q->forNorma($norma);
                        }
                    );
                },
            ]);
    }

    // /**
    //  * @param Builder              $query
    //  * @param Organisation         $organisation
    //  * @param array<string, mixed> $referenceFilters
    //  *
    //  * @return Builder
    //  */
    // public function scopeForOrganisation(Builder $query, Organisation $organisation, array $referenceFilters = []): Builder
    // {
    //     return $query->whereHas('locations.references', function ($q) use ($organisation, $referenceFilters) {
    //         $q->forOrganisation($organisation);
    //         if (!empty($referenceFilters)) {
    //             $q->filter($referenceFilters);
    //         }
    //     });
    // }

    /**
     * @param Builder              $query
     * @param Organisation         $organisation
     * @param User                 $user
     * @param array<string, mixed> $referenceFilters
     *
     * @return Builder
     */
    public function scopeForOrganisationUserAccess(Builder $query, Organisation $organisation, User $user, array $referenceFilters = []): Builder
    {
        return $query->whereHas('locations.references', function ($q) use ($organisation, $user, $referenceFilters) {
            $q->forOrganisationUserAccess($organisation, $user);
            if (!empty($referenceFilters)) {
                $q->filter($referenceFilters);
            }
        });
    }

    /**
     * @param Builder              $query
     * @param Organisation         $organisation
     * @param User                 $user
     * @param array<string, mixed> $referenceFilters
     *
     * @return Builder
     */
    public function scopeForOrganisationUserAccessWithLocations(Builder $query, Organisation $organisation, User $user, array $referenceFilters = []): Builder
    {
        return $query->forOrganisationUserAccess($organisation, $user, $referenceFilters)
            ->with([
                'locations' => function ($q) use ($organisation, $user) {
                    $q->whereHas('references', fn ($q) => $q->forOrganisationUserAccess($organisation, $user));
                },
            ]);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
        ];
    }
}
