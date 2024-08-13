<?php

namespace App\Http\Resources\Corpus\Work\V2;

use App\Http\Resources\Corpus\Reference\V2\LegalReportReferenceResource;
use App\Http\Resources\Geonames\Location\V2\LocationResource;
use App\Http\Resources\Ontology\LegalDomain\V2\LegalDomainResource;
use App\Models\Corpus\Work;

/**
 * @mixin Work
 */
class LegalReportWorkResource extends WorkResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return array_merge(
            parent::toArray($request),
            [
                'citations' => LegalReportReferenceResource::collection($this->whenLoaded('references')),
                'locations' => LocationResource::collection($this->whenLoaded('references', $this->references->pluck('locations')->collapse()->unique('id'))),
                'children' => self::collection($this->whenLoaded('children')),
                'legalDomains' => LegalDomainResource::collection($this->whenLoaded('references', $this->references->pluck('legalDomains')->collapse()->unique('id'))),
            ],
        );
    }
}
