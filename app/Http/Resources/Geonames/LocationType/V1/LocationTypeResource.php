<?php

namespace App\Http\Resources\Geonames\LocationType\V1;

use App\Models\Geonames\LocationType;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Only to be used in V1 endpoint for legal report.
 *
 * @mixin LocationType
 */
class LocationTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>|\Illuminate\Contracts\Support\Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'adjective_key' => $this->adjective_key,
        ];
    }
}
