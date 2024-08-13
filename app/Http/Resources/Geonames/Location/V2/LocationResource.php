<?php

namespace App\Http\Resources\Geonames\Location\V2;

use App\Http\Resources\Geonames\LocationType\V2\LocationTypeResource;
use App\Models\Geonames\Location;
use Illuminate\Http\Resources\Json\JsonResource;

/**
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
            'parent_id' => $this->parent_id,
            'location_type_id' => $this->location_type_id,
            'location_country_id' => $this->location_country_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'level' => $this->level,
            'flag' => $this->flag,
            'location_code' => $this->location_code,
            'locationType' => $this->when($this->relationLoaded('type') || $this->relationLoaded('locationType'), function () {
                return new LocationTypeResource($this->relationLoaded('type') ? $this->type : $this->locationType);
            }),
            'country' => new self($this->whenLoaded('country')),
        ];
    }
}
