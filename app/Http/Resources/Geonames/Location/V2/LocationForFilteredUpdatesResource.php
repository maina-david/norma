<?php

namespace App\Http\Resources\Geonames\Location\V2;

use App\Models\Geonames\Location;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Legacy: Only to be used by filtered legal-updates endpoint - used by cleanchain.
 *
 * @mixin Location
 */
class LocationForFilteredUpdatesResource extends JsonResource
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
            'location_country_id' => $this->location_country_id,
            'title' => $this->title,
            'flag' => $this->flag,
        ];
    }
}
