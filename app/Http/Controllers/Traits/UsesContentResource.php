<?php

namespace App\Http\Controllers\Traits;

use App\Models\Corpus\ContentResource;
use App\Services\Corpus\PrepareContentHtml;
use App\Stores\Corpus\ContentResourceStore;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;

trait UsesContentResource
{
    /**
     * Render the content for the given resource and target.
     *
     * @param \App\Models\Corpus\ContentResource $resource
     * @param int                                $targetId
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return \HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse
     */
    public function getContentResource(ContentResource $resource, int $targetId): PendingTurboStreamResponse
    {
        $content = null;

        if ($resource->isHtml() && !is_null($resource->path)) {
            $content = app(ContentResourceStore::class)->get($resource->path);
            $content = $content ? app(PrepareContentHtml::class)->cleanHtmlContent($content) : '';
        }

        $isPdf = $resource->isPdf();

        return singleTurboStreamResponse("content-full-text-{$targetId}")
            ->view('partials.corpus.content-resource.show', [
                'content' => $isPdf ? null : $content,
                'isPdf' => $isPdf,
                'path' => $resource->path,
            ]);
    }
}
