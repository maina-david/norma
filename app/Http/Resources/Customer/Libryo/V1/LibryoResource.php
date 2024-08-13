<?php

namespace App\Http\Resources\Customer\Libryo\V1;

use App\Http\Resources\AbstractResource;
use App\Http\Resources\Geonames\Location\V2\LocationResource;
use App\Models\Customer\Libryo;
use Illuminate\Http\Request;

/**
 * App\Models\Customer\Libryo.
 *
 * @mixin Libryo
 */
class LibryoResource extends AbstractResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return array_merge([
            'id' => (int) $this->id,
            'title' => $this->title,
            'place_type_id' => $this->place_type_id ? (int) ($this->place_type_id) : null,
            'geo_lat' => $this->geo_lat,
            'geo_lng' => $this->geo_lng,
            'address' => $this->address,
            'location_id' => $this->location_id ? (int) ($this->location_id) : null,
            'library_id' => $this->library_id ? (int) ($this->library_id) : null,
            'organisation_id' => $this->organisation_id ?? (int) $this->organisation_id,
            'deactivated' => (bool) $this->deactivated,
            'description' => $this->description,
            'compilation_in_progress' => (bool) $this->compilation_in_progress,
            'location' => new LocationResource($this->whenLoaded('location')),
        ], $this->timestamps());
    }
}
