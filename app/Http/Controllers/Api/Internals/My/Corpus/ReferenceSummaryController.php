<?php

namespace App\Http\Controllers\Api\Internals\My\Corpus;

use App\Http\Resources\Internals\My\Corpus\ReferenceSummaryResource;
use App\Models\Corpus\Reference;
use App\Models\Requirements\Summary;
use App\Traits\TranslatesReferenceContent;

class ReferenceSummaryController
{
    use TranslatesReferenceContent;

    /**
     * Get the summary if present.
     *
     * @param \App\Models\Corpus\Reference $reference
     * @param string|null                  $language
     *
     * @return \App\Http\Resources\Internals\My\Corpus\ReferenceSummaryResource
     */
    public function show(Reference $reference, ?string $language = null): ReferenceSummaryResource
    {
        $summary = Summary::where('reference_id', $reference->id)
            ->select(['summary_body'])
            ->firstOrFail();

        if ($language) {
            $summary->summary_body = $this->translate($reference, $summary->summary_body, $language, $summary);
        }

        return new ReferenceSummaryResource($summary);
    }
}
