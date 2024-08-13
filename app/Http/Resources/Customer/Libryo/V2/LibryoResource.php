<?php

namespace App\Http\Resources\Customer\Libryo\V2;

use App\Http\Resources\AbstractResource;
use App\Http\Resources\Geonames\Location\V2\LocationResource;
use App\Http\Resources\Ontology\LegalDomain\V2\LegalDomainResource;
use App\Models\Customer\Libryo;

/**
 * @mixin Libryo
 */
class LibryoResource extends AbstractResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
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
            'organisation_id' => $this->organisation_id ?? (int) $this->organisation_id,
            'deactivated' => (bool) $this->deactivated,
            'description' => $this->description,
            'compilation_in_progress' => (bool) $this->compilation_in_progress,
            'location' => new LocationResource($this->whenLoaded('location')),
            'legalDomains' => LegalDomainResource::collection($this->whenLoaded('legalDomains')),
        ], $this->timestamps());
    }
}
