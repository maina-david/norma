<?php

namespace App\Models\Traits;

use App\Models\Geonames\Location;
use Illuminate\Database\Eloquent\Builder;

trait LimitsLocations
{
    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param Builder  $query
     * @param Location $location
     *
     * @return Builder
     */
    public function scopeLimitedLocationAncestors(Builder $query, Location $location): Builder
    {
        return $query->where(function ($q) use ($location) {
            $q->doesntHave('locations');
            $q->orWhereHas('locations', function ($q) use ($location) {
                $ids = $location->ancestors->pluck('id')->toArray();
                $ids[] = $location->id;

                $q->whereKey($ids);
            });
        });
    }
}
