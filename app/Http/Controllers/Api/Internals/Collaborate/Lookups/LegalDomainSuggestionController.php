<?php

namespace App\Http\Controllers\Api\Internals\Collaborate\Lookups;

use App\Enums\Lookups\ContentMetaSuggestions;
use App\Http\Resources\Internals\Collaborate\Ontology\LegalDomainResource;
use App\Traits\UsesNormaAISuggestions;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LegalDomainSuggestionController
{
    use UsesNormaAISuggestions;

    /**
     * Get the suggestions.
     *
     * @param int $referenceId
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(int $referenceId): AnonymousResourceCollection
    {
        $items = $this->suggestFromReferenceContent(ContentMetaSuggestions::DOMAIN, $referenceId);

        return LegalDomainResource::collection($items);
    }
}
