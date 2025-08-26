<?php

namespace App\Http\Controllers\Api\Internals\Collaborate\Lookups;

use App\Enums\Lookups\ContentMetaSuggestions;
use App\Http\Resources\Internals\Collaborate\Compilation\ContextQuestionResource;
use App\Traits\UsesNormaAISuggestions;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContextQuestionSuggestionController
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
        $items = $this->suggestFromReferenceContent(ContentMetaSuggestions::CONTEXT, $referenceId);

        return ContextQuestionResource::collection($items);
    }
}
