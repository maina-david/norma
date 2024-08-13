<?php

namespace App\Http\Controllers\Corpus\Collaborate\ToC;

use App\Http\Controllers\Corpus\Collaborate\WorkflowTaskController;
use App\Models\Corpus\WorkExpression;
use App\Services\Corpus\WorkExpressionContentManager;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ToCContentController extends WorkflowTaskController
{
    /**
     * Get the content of the active expression.
     *
     * @param WorkExpression $expression
     * @param int            $volume
     *
     * @return Response
     */
    public function show(WorkExpression $expression, int $volume): Response
    {
        $this->abortNotJson();

        /** @var string $content */
        $content = __('corpus.work.no_preview_available');

        if ($volume <= $expression->volume) {
            $content = $expression->getVolume($volume) ?? $content;
        }

        /** @var Response */
        return response($content, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Update the work expression content.
     *
     * @param Request                      $request
     * @param WorkExpressionContentManager $manager
     * @param WorkExpression               $expression
     * @param int                          $volume
     *
     * @throws AuthorizationException
     *
     * @return Response
     */
    public function update(Request $request, WorkExpressionContentManager $manager, WorkExpression $expression, int $volume): Response
    {
        $task = $this->task();

        $this->authorize('editToC', [$expression, $task]);

        $content = (string) $request->getContent();

        $manager->saveVolume($expression, $volume, $content);

        if (strlen($content) > 20) {
            $expression->update(['show_source_document' => false]);
        }

        /** @var Response */
        return response($expression->getVolume($volume), 200, ['Content-Type' => 'text/html']);
    }
}
