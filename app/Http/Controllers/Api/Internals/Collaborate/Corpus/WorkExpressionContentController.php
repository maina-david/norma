<?php

namespace App\Http\Controllers\Api\Internals\Collaborate\Corpus;

use App\Http\Resources\Internals\Collaborate\PaginatorResource;
use App\Managers\AppManager;
use App\Models\Corpus\WorkExpression;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class WorkExpressionContentController
{
    /**
     * Get the expression content.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     *
     * @return AnonymousResourceCollection<array<string, string>>
     */
    public function show(Request $request, WorkExpression $expression): AnonymousResourceCollection
    {
        $volume = (int) $request->query('page', '1');
        $expression->load(['doc']);

        $body = ['content' => null, 'doc' => $expression->doc];

        if ($expression->doc) {
            // @codeCoverageIgnoreStart
            $prefix = AppManager::getApp();

            $resourceLink = $expression->doc->tocItems()->count() === 0 && $expression->doc->firstContentResource
                ? route("{$prefix}.content-resources.show", [
                    'resource' => $expression->doc->firstContentResource,
                    'targetId' => $expression->doc->id,
                ])
                : null;

            $body['doc'] = [
                'id' => $expression->doc->id,
                'title' => $expression->doc->title,
                'title_translation' => $expression->doc->docMeta->title_translation,
                'resource_link' => $resourceLink,
            ];
        // @codeCoverageIgnoreEnd
        } else {
            /** @var string $content */
            $content = __('corpus.work.no_preview_available');
            if ($expression->work->source_url) {
                $content .= ' <a href="' . $expression->work->source_url . '" target="_blank">' . __('corpus.work.link_to_original') . '</a>';
                $content .= '<br/>' . __('corpus.work.attempting_to_preview') . ':';
                $content .= ' <embed class="w-full min-h-screen" src="' . $expression->work->source_url . '"></embed>';
            }

            if ($volume <= $expression->volume) {
                $content = $expression->getVolume($volume) ?? $content;
            }

            $body['content'] = $content;
        }

        return PaginatorResource::collection(new LengthAwarePaginator([$body], $expression->volume, 1, $volume, [
            'path' => route('collaborate.work-expressions.volume.show', ['expression' => $expression->id]),
        ]));
    }
}
