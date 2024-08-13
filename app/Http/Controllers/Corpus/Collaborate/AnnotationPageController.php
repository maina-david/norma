<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Http\Controllers\Traits\AnnotatesWorks;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Task;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnotationPageController extends WorkflowTaskController
{
    use AnnotatesWorks;

    /**
     * Get the annotation view.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     * @param Task|null      $task
     *
     * @throws AuthorizationException
     *
     * @return View
     */
    public function index(Request $request, WorkExpression $expression, ?Task $task = null): View
    {
        $this->switchAndAuthorise('annotate', $expression, $task);
        $filters = $this->getFiltersQueryAndMap();
        $pageMeta = $this->getPageMeta($expression, $filters);
        $filters = $this->getAppliedFilters($request, $filters);

        $expression->load(['doc.firstContentResource.textContentResource']);

        /** @var View */
        return view('pages.corpus.collaborate.annotations.index', [
            'expression' => $expression,
            'task' => $task,
            'filters' => $filters,
            'pageMeta' => $pageMeta,
            'hasNotes' => $this->workHasNotes($expression),
            'plainTextSource' => $expression->doc->firstContentResource->textContentResource->path ?? null,
        ]);
    }

    /**
     * Get the annotation view.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     *
     * @return View
     */
    public function indexForPreview(Request $request, WorkExpression $expression): View
    {
        $this->addPreviewing($expression->id);
        $this->resetMetaAndFilters();

        $expression->load(['doc.firstContentResource.textContentResource']);

        /** @var View */
        return view('pages.corpus.collaborate.annotations.index', [
            'expression' => $expression,
            'task' => null,
            'filters' => [],
            'pageMeta' => [],
            'preview' => true,
            'hasNotes' => $this->workHasNotes($expression),
            'plainTextSource' => $expression->doc->firstContentResource->textContentResource->path ?? null,
        ]);
    }

    /**
     * Redirect to the annotation page from the work.
     *
     * @param Request $request
     * @param Work    $work
     *
     * @return RedirectResponse
     */
    public function fromWork(Request $request, Work $work): RedirectResponse
    {
        /** @var array<string, mixed> */
        $filters = $request->query();

        if ($work->active_work_expression_id) {
            return redirect()->route('collaborate.annotations.index', ['expression' => $work->active_work_expression_id, ...$filters]);
        }

        /** @var WorkExpression $expression */
        $expression = $work->expressions()->orderBy('start_date', 'desc')->firstOrFail();

        return redirect()->route('collaborate.annotations.index', ['expression' => $expression->id, ...$filters]);
    }
}
