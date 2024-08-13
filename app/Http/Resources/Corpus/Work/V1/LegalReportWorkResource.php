<?php

namespace App\Http\Resources\Corpus\Work\V1;

use App\Http\Resources\Corpus\Reference\V1\LegalReportReferenceResource;
use App\Http\Resources\Geonames\Location\V1\LocationResource;
use App\Http\Resources\Ontology\LegalDomain\V1\LegalDomainResource;
use App\Models\Corpus\Work;
use Illuminate\Http\Request;

/**
 * @mixin Work
 */
class LegalReportWorkResource extends WorkResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return array_merge(
            parent::toArray($request),
            [
                'registerItems' => [
                    'data' => LegalReportReferenceResource::collection($this->references),
                ],
                'locations' => [
                    // as this is a legacy end point, we're not going to bother with resource collections here.
                    'data' => LocationResource::collection($this->whenLoaded('references', $this->references->pluck('locations')->collapse()->unique('id'))),
                ],
                'children' => [
                    // as this is a legacy end point, we're not going to bother with resource collections here.
                    'data' => self::collection($this->whenLoaded('children', $this->children)),
                ],
                'legalDomains' => [
                    // as this is a legacy end point, we're not going to bother with resource collections here.
                    'data' => LegalDomainResource::collection($this->whenLoaded('references', $this->references->pluck('legalDomains')->collapse()->unique('id'))),
                ],
            ]
        );
    }
}
