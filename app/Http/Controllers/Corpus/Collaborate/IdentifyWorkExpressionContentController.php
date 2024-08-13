<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Models\Corpus\WorkExpression;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Http\Request;

class IdentifyWorkExpressionContentController
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

        /** @var PendingTurboStreamResponse */
        return singleTurboStreamResponse('work_expression_content', 'update')
            ->view('pages.corpus.collaborate.identify-references.partials.content', [
                'doc' => $expression->doc,
                'expression' => $expression,
            ]);
    }
}
