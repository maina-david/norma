<?php

namespace App\Http\Resources\Assess\AssessmentItem\V2;

use App\Http\Resources\AbstractResource;
use App\Http\Resources\Corpus\Reference\V2\BasicReferenceResource;
use App\Http\Resources\Ontology\LegalDomain\V2\LegalDomainResource;
use App\Models\Assess\AssessmentItem;

/**
 * @mixin AssessmentItem
 */
class AssessmentItemResource extends AbstractResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->toDescription(),
            'frequency' => $this->frequency,
            'risk_rating' => $this->risk_rating,
            'updated_at' => $this->formatDate($this->updated_at),
            'basic_citations' => BasicReferenceResource::collection($this->whenLoaded('references')),
            'legal_domain' => new LegalDomainResource($this->whenLoaded('legalDomain')),
        ];
    }
}
