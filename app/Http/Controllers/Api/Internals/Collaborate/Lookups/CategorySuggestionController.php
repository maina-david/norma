<?php

namespace App\Http\Controllers\Api\Internals\Collaborate\Lookups;

use App\Enums\Lookups\ContentMetaSuggestions;
use App\Http\Resources\Internals\Collaborate\Ontology\CategoryResource;
use App\Traits\UsesNormaAISuggestions;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategorySuggestionController
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
        $items = $this->suggestFromReferenceContent(ContentMetaSuggestions::CATEGORY, $referenceId);

        return CategoryResource::collection($items);
    }
}
