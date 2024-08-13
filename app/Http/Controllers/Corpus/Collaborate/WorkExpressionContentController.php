<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Http\Controllers\Corpus\ContentResourceController;
use App\Models\Corpus\WorkExpression;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class WorkExpressionContentController
{
    /**
     * Get the expression content.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     *
     * @return PendingTurboStreamResponse
     */
    public function show(Request $request, WorkExpression $expression): PendingTurboStreamResponse
    {
        abort_unless($request->isForTurboFrame(), 404);

        $pagination = null;
        if (!$expression->doc) {
            $volume = (int) $request->query('volume', '1');

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

            $pagination = new LengthAwarePaginator([$content], $expression->volume, 1, $volume, [
                'pageName' => 'volume',
                'path' => route('collaborate.work-expressions.volume.show', ['expression' => $expression->id]),
            ]);
        }

        /** @var PendingTurboStreamResponse */
        return singleTurboStreamResponse('work_expression_content', 'update')
            ->view('pages.corpus.collaborate.annotations.partials.content', [
                'content' => $pagination,
                'doc' => $expression->doc,
            ]);
    }

    /**
     * Show the source document.
     *
     * @param WorkExpression $expression
     *
     * @return Response
     */
    public function showSource(WorkExpression $expression): Response
    {
        $expression->load(['sourceDocument', 'doc']);

        if ($expression->doc) {
            $path = $expression->doc->firstContentResource?->path;
            if (!$path) {
                // @codeCoverageIgnoreStart
                $content = __('corpus.work.no_preview_available');
                if ($expression->work->source_url) {
                    $content .= '<br/> <a href="' . $expression->work->source_url . '" target="_blank">' . __('corpus.work.link_to_original') . '</a>';
                    $content .= '<br/>' . __('corpus.work.attempting_to_preview') . ':';
                    $content .= '<br/> <embed style="margin-top: 10px; width: 100%; min-height: 90vh;" src="' . $expression->work->source_url . '"></embed>';
                }

                return response($content, 200)
                    ->header('Content-Type', 'text/html');
                // @codeCoverageIgnoreEnd
            }

            return app(ContentResourceController::class)->streamContent($path);
        }

        /** @var string $content */
        $content = __('corpus.work.no_preview_available');
        $mime = 'text/plain';

        $viewable = [
            'text/plain', 'application/pdf', 'text/html', 'html/text', 'image/png', 'image/jpeg',
        ];

        if (in_array($expression->sourceDocument->mime_type ?? '', $viewable)) {
            $content = $expression->getSourceDocument() ?? $content;
            $mime = $expression->sourceDocument?->mime_type;
        }

        /** @var Response */
        return response($content, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename=document',
        ]);
    }
}
