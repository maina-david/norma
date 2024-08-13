<?php

namespace App\Http\Resources\Internals\Collaborate\Geonames;

use App\Models\Corpus\Pivots\LocationReferenceDraft;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property LocationReferenceDraft $pivot
 *
 * @mixin \App\Models\Geonames\Location
 */
class LocationResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'change_status' => $this->whenPivotLoaded(LocationReferenceDraft::class, fn () => $this->pivot->change_status),
        ];
    }
}
