<?php

namespace App\Http\Controllers\Api\Internals\Collaborate\Corpus;

use App\Http\Requests\Corpus\ReferenceContentRequest;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContentDraft;
use App\Services\HTMLPurifierService;
use Illuminate\Http\JsonResponse;

class ReferenceContentController
{
    /**
     * Get the cached content.
     *
     * @param Reference $reference
     *
     * @return JsonResponse
     */
    public function show(Reference $reference): JsonResponse
    {
        $reference->load(['htmlContent', 'contentDraft', 'contentVersions.versionContentHtml']);
        $versions = $reference->contentVersions
            ->map(fn ($version) => [
                'id' => $version->id,
                'created_at' => $version->created_at,
                'title' => $version->title,
                'html_content' => $version->versionContentHtml->html_content ?? null,
            ])
            ->all();

        return response()->json([
            'live' => $reference->htmlContent->cached_content ?? null,
            'draft' => [
                'html_content' => $reference->contentDraft->html_content ?? null,
                'title' => $reference->contentDraft->title ?? null,
            ],
            'versions' => $versions,
        ]);
    }

    /**
     * Update the content.
     *
     * @param \App\Http\Requests\Corpus\ReferenceContentRequest $request
     * @param \App\Models\Corpus\Reference                      $reference
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ReferenceContentRequest $request, Reference $reference): JsonResponse
    {
        $content = app(HTMLPurifierService::class)->cleanSection($request->get('content') ?? '');

        ReferenceContentDraft::updateOrCreate(['reference_id' => $reference->id], [
            'reference_id' => $reference->id,
            'title' => trim(strip_tags($request->get('title'))),
            'html_content' => $content,
            'author_id' => $request->user()?->id,
        ]);

        return $this->show($reference);
    }
}
