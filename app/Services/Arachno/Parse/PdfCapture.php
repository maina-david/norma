<?php

namespace App\Services\Arachno\Parse;

use App\Models\Corpus\Doc;
use App\Stores\Corpus\ContentResourceStore;
use Throwable;

class PdfCapture
{
    public function __construct(
        protected ContentResourceStore $contentResourceStore,
    ) {
    }

    /**
     * @codeCoverageIgnore
     */
    public function capture(Doc $doc, string $content): void
    {
        $resource = $this->contentResourceStore->storeResource($content, 'application/pdf');

        try {
            $doc->startLink?->contentResources()->attach($resource);
            // @codeCoverageIgnoreStart
        } catch (Throwable $th) {
        }

        $doc->first_content_resource_id = $resource->id;
        // @codeCoverageIgnoreEnd
    }
}
