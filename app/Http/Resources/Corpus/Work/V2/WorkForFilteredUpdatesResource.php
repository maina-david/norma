<?php

namespace App\Http\Resources\Corpus\Work\V2;

use App\Http\Resources\Geonames\Location\V2\LocationForFilteredUpdatesResource;
use App\Http\Resources\Ontology\LegalDomain\V2\LegalDomainResource;
use App\Models\Corpus\Work;

/**
 * Legacy: Only to be used by filtered legal-updates endpoint.
 *
 * @mixin Work
 */
class WorkForFilteredUpdatesResource extends WorkResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            ...parent::toArray($request),
            ...[
                'workReference' => [
                    'id' => null,
                    'legalDomains' => LegalDomainResource::collection($this['for_update_legal_domains']),
                    'locations' => LocationForFilteredUpdatesResource::collection($this['for_update_locations']),
                ],
            ],
        ];
    }
}
