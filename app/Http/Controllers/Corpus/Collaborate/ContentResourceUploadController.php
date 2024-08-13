<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Http\Controllers\Controller;
use App\Stores\Corpus\ContentResourceStore;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class ContentResourceUploadController extends Controller
{
    public function __construct(
        protected ContentResourceStore $contentResourceStore,
    ) {
    }

    public function upload(Request $request): PendingTurboStreamResponse
    {
        /** @var UploadedFile $file */
        $file = $request->file('file');

        $mimeType = $file->getClientMimeType();
        $contentResource = $this->contentResourceStore
            ->storeResource($file->getContent(), $mimeType);

        $url = $this->contentResourceStore->getLinkForResource($contentResource);

        return singleTurboStreamResponse('content_resource_uploader_url', 'update')
            ->view('partials.corpus.collaborate.content-resource.content-resource-url', [
                'url' => $url,
            ]);
    }
}
