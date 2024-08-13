<?php

namespace App\Http\Resources\Geonames\Location\V1;

use App\Http\Resources\Geonames\LocationType\V1\LocationTypeResource;
use App\Models\Geonames\Location;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Only to be used in V1 endpoint for legal report.
 *
 * @mixin Location
 */
class LocationResource extends JsonResource
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
        return [
            'id' => $this->id,
            'title' => $this->title,
            'location_type_id' => $this->location_type_id,
            'flag' => $this->flag,
            'parent_id' => $this->parent_id,
            'level' => $this->level,
            'location_country_id' => $this->location_country_id,
            'location_code' => $this->location_code,
            'country' => [
                // as this is a legacy end point, we're not going to bother with resource collections here.
                'data' => new self($this->whenLoaded('country')),
            ],
            'locationType' => [
                // as this is a legacy end point, we're not going to bother with resource collections here.
                'data' => new LocationTypeResource($this->whenLoaded('type')),
            ],
        ];
    }
}
