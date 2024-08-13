<?php

namespace App\Http\Controllers\Corpus;

use App\Http\Controllers\Controller;
use App\Models\Corpus\ContentResource;
use App\Services\Corpus\PrepareContentHtml;
use App\Stores\Corpus\ContentResourceStore;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Str;

class ContentResourceController extends Controller
{
    public function __construct(
        protected ContentResourceStore $contentResourceStore,
        protected PrepareContentHtml $prepareContentHtml,
    ) {
    }

    public function streamContent(string $path): Response
    {
        $resourceHash = Str::afterLast($path, '/');

        /** @var ContentResource */
        $resource = ContentResource::forUid($resourceHash)->firstOrFail();

        $content = $resource->path ? $this->contentResourceStore->get($resource->path) : '';
        $content = $content ?? '';
        if ($resource->isHtml()) {
            $content = $this->prepareContentHtml->cleanHtmlContent($content);
        }

        return ResponseFacade::make($content, 200, [
            'Content-Type' => $resource->mime_type,
            'Cache-Control' => 'max-age=31536000, immutable', // we can cache these in the browser forever as they use content hashed address
        ]);
    }
}
