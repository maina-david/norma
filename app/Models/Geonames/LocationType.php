<?php

namespace App\Models\Geonames;

use App\Models\AbstractModel;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
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
     * @param \App\Models\Customer\Libryo           $libryo
     * @param array<string, mixed>                  $referenceFilters
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForLibryoViaUpdates(Builder $query, Libryo $libryo, array $referenceFilters = []): Builder
    {
        return $query->whereHas('locations.references', function ($builder) use ($libryo, $referenceFilters) {
            $builder->whereIn('id', $libryo->legalUpdates()->select('notify_reference_id'));
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
        LegalUpdate::whereRelation('libryos', 'organisation_id', $organisation->id)->select('notify_reference_id');

        return $query->whereHas('locations.references', function ($builder) use ($user, $organisation, $referenceFilters) {
            $subQuery = LegalUpdate::select('notify_reference_id')
                ->whereHas('libryos', function ($query) use ($user, $organisation) {
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
     * @param Libryo               $libryo
     * @param array<string, mixed> $referenceFilters
     *
     * @return Builder
     */
    public function scopeForLibryo(Builder $query, Libryo $libryo, array $referenceFilters = []): Builder
    {
        return $query->whereHas('locations.references', function ($q) use ($libryo, $referenceFilters) {
            $q->forLibryo($libryo);
            if (!empty($referenceFilters)) {
                $q->filter($referenceFilters);
            }
        });
    }

    /**
     * @param Builder              $query
     * @param Libryo               $libryo
     * @param array<string, mixed> $referenceFilters
     *
     * @return Builder
     */
    public function scopeForLibryoWithLocations(Builder $query, Libryo $libryo, array $referenceFilters = []): Builder
    {
        return $query->forLibryo($libryo, $referenceFilters)
            ->with([
                'locations' => function ($q) use ($libryo) {
                    $q->whereHas(
                        'references',
                        function ($q) use ($libryo) {
                            $q->forLibryo($libryo);
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
