<?php

namespace App\Http\Resources\Corpus\Reference\V2;

use App\Http\Resources\IdResource;

/**
 * Only used for forOrganisation end point, which is only used by Isometrix.
 */
class BasicReferenceForOrgResource extends BasicReferenceResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string,mixed>
     */
    public function toArray($request): array
    {
        return array_merge(
            parent::toArray($request),
            [
                'normas' => IdResource::collection($this->whenLoaded('normas')),
                'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
                'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            ]
        );
    }
}
