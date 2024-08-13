<?php

namespace App\Http\Controllers\Api\V2\Geonames;

use App\Http\Controllers\Controller;
use App\Http\Requests\Geonames\LocationSearchRequest;
use App\Http\Resources\Geonames\Location\V2\LocationResource;
use App\Models\Geonames\Location;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LocationSearchController extends Controller
{
    /**
     * Search for the locations.
     *
     * @param LocationSearchRequest $request
     *
     * @return AnonymousResourceCollection<Location>
     */
    public function index(LocationSearchRequest $request): AnonymousResourceCollection
    {
        /** @var Location $country */
        $country = Location::where('slug', $request->get('country'))->first();

        $locations = Location::where('level', $request->get('level'))
            ->where('location_country_id', $country->id)
            ->ancestorNames($request->only(['level_2', 'level_3']))
            ->nameMatches($request->get('name'))
            ->take(50)
            ->get();

        return LocationResource::collection($locations);
    }
}
