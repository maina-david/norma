<?php

namespace App\Traits;

use App\Enums\Lookups\ContentMetaSuggestions;
use App\Http\Services\LibryoAI\Client;
use App\Models\Actions\ActionArea;
use App\Models\Assess\AssessmentItem;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\ReferenceContent;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use Illuminate\Support\Collection;

trait UsesLibryoAISuggestions
{
    /**
     * Use the reference content to get suggestions.
     *
     * @param \App\Enums\Lookups\ContentMetaSuggestions $suggestion
     * @param int                                       $referenceId
     *
     * @return \Illuminate\Support\Collection
     */
    protected function suggestFromReferenceContent(ContentMetaSuggestions $suggestion, int $referenceId): Collection
    {
        /** @var \App\Models\Corpus\ReferenceContent|null $text */
        $text = ReferenceContent::where('reference_id', $referenceId)->first();

        return $this->suggest($suggestion, $text->cached_content ?? '');
    }

    /**
     * Suggest from LibryoAI the given suggestion type.
     *
     * @param \App\Enums\Lookups\ContentMetaSuggestions $suggestion
     * @param string                                    $text
     *
     * @return \Illuminate\Support\Collection
     */
    protected function suggest(ContentMetaSuggestions $suggestion, string $text): Collection
    {
        $text = trim(strip_tags($text));

        if (empty($text)) {
            return collect([]);
        }

        $response = (new Client())->suggest($suggestion, $text)->all();

        if (empty($response)) {
            return collect([]);
        }

        $query = match ($suggestion) {
            ContentMetaSuggestions::ASSESS => new AssessmentItem(),
            ContentMetaSuggestions::CATEGORY => new Category(),
            ContentMetaSuggestions::CONTEXT => new ContextQuestion(),
            ContentMetaSuggestions::DOMAIN => new LegalDomain(),
            ContentMetaSuggestions::ACTION => new ActionArea(),
        };

        return $query->whereKey($response)
            ->get()
            ->sortBy(fn ($item) => array_search($item->id, $response))
            ->values();
    }
}
