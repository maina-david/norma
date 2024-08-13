<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Http\Controllers\Controller;
use App\Models\Corpus\WorkExpression;
use Illuminate\View\View;

class WorkExpressionPreviewController extends Controller
{
    /**
     * Get the work expression preview.
     *
     * @param WorkExpression $expression
     *
     * @return View
     */
    public function show(WorkExpression $expression): View
    {
        /** @var View */
        return view('pages.corpus.collaborate.work-expression.preview', [
            'expression' => $expression,
            'task' => null,
        ]);
    }
}
