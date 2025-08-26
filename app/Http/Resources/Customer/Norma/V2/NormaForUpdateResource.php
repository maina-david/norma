<?php

namespace App\Http\Resources\Customer\Norma\V2;

use App\Http\Resources\AbstractResource;
use App\Models\Customer\Norma;

/**
 * @mixin Norma
 */
class NormaForUpdateResource extends AbstractResource
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
            'id' => (int) $this->id,
            'title' => $this->title,
            'geo_lat' => $this->geo_lat,
            'geo_lng' => $this->geo_lng,
            'address' => $this->address,
            'location_id' => $this->location_id ? (int) ($this->location_id) : null,
            'organisation_id' => $this->organisation_id ?? (int) $this->organisation_id,
        ];
    }
}
