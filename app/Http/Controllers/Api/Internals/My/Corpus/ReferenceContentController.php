<?php

namespace App\Http\Controllers\Api\Internals\My\Corpus;

use App\Http\Resources\Internals\My\Corpus\ReferenceContentResource;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Traits\TranslatesReferenceContent;

class ReferenceContentController
{
    use TranslatesReferenceContent;

    /**
     * Get the content for the given reference.
     *
     * @param \App\Models\Corpus\Reference $reference
     * @param string|null                  $language
     *
     * @return ReferenceContentResource
     */
    public function show(Reference $reference, ?string $language = null): ReferenceContentResource
    {
        $content = ReferenceContent::where('reference_id', $reference->id)
            ->select(['cached_content'])
            ->firstOrFail();

        if ($language) {
            $content->cached_content = $this->translate($reference, $content->cached_content, $language);
        }

        return new ReferenceContentResource($content);
    }
}
