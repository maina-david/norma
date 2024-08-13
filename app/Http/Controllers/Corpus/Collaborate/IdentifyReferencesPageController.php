<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Enums\Corpus\ReferenceType;
use App\Http\Controllers\Traits\AnnotatesWorks;
use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Task;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IdentifyReferencesPageController extends WorkflowTaskController
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
        $this->switchAndAuthorise('bespokeReferences', $expression, $task);
        $filters = $this->getFiltersQueryAndMap();
        $pageMeta = $this->getPageMeta($expression, $filters);
        $filters = $this->getAppliedFilters($request, $filters);

        $expression->load(['doc.firstContentResource.textContentResource']);

        $workReference = Reference::where('work_id', $expression->work_id)
            ->where('type', ReferenceType::work()->value)
            ->first();

        /** @var View */
        return view('pages.corpus.collaborate.identify-references.vue-index', [
            'expression' => $expression,
            'task' => $task,
            'filters' => $filters,
            'pageMeta' => $pageMeta,
            'workReference' => $workReference->id ?? null,
            'hasNotes' => $this->workHasNotes($expression),
            'plainTextSource' => $expression->doc->firstContentResource->textContentResource->path ?? null,
        ]);
    }
}
