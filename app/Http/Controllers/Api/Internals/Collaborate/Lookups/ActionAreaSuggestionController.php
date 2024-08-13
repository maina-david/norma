<?php

namespace App\Http\Controllers\Api\Internals\Collaborate\Lookups;

use App\Enums\Lookups\ContentMetaSuggestions;
use App\Http\Resources\Internals\My\Actions\ActionAreaResource;
use App\Traits\UsesLibryoAISuggestions;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActionAreaSuggestionController
{
    use UsesLibryoAISuggestions;

    /**
     * Get the suggestions.
     *
     * @param int $referenceId
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(int $referenceId): AnonymousResourceCollection
    {
        $items = $this->suggestFromReferenceContent(ContentMetaSuggestions::ACTION, $referenceId);

        return ActionAreaResource::collection($items);
    }
}
