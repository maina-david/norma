<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Enums\Corpus\ReferenceType;
use App\Http\Controllers\Traits\UsesAnnotationPage;
use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use App\Models\Ontology\CategoryType;
use App\Models\Workflows\Task;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class WorkExpressionReferenceController extends WorkflowTaskController
{
    use UsesAnnotationPage;

    /** @var string[] */
    protected array $multiFilters = [
        'assessmentItemsWithDrafts', 'topicsWithDrafts', 'questionsWithDrafts', 'tagsWithDrafts',
        'locationsWithDrafts', 'domainsWithDrafts', 'actionAreasWithDrafts',
    ];

    /**
     * Get the common base query.
     *
     * @param WorkExpression $expression
     *
     * @return Builder
     */
    protected function baseQuery(WorkExpression $expression): Builder
    {
        /** @var Builder */
        return Reference::where('work_id', $expression->work_id)
            ->whereIn('type', [
                ReferenceType::work()->value,
                ReferenceType::citation()->value,
            ])
            ->select([
                'id', 'work_id', 'volume', 'level', 'type', 'status',
            ])
            ->with([
                'refSelector:reference_id,selectors',
                'refRequirement:reference_id',
                'requirementDraft:reference_id,change_status',
                'refSelector:reference_id,selectors',
                'refPlainText:reference_id,plain_text',
            ])
            ->withCount(['linkedParents', 'linkedChildren'])
            ->orderBy('volume')
            ->orderBy('start');
    }

    /**
     * Get the filters from the current request.
     *
     * @param Request $request
     *
     * @return array<string, string>
     */
    protected function getFiltersFromRequest(Request $request): array
    {
        /** @var array<string,mixed> $query */
        $query = $request->query();

        return collect($query)
            ->filter(fn ($item, $key) => str_starts_with($key, 'has') || in_array($key, $this->multiFilters))
            ->toArray();
    }

    /**
     * Set the filters to be used.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     *
     * @return RedirectResponse
     */
    public function setFilters(Request $request, WorkExpression $expression): RedirectResponse
    {
        $this->setReferenceFilters($this->getFiltersFromRequest($request));

        /** @var array<string,mixed> $query */
        $query = $request->query();

        return redirect()->route('collaborate.work-expressions.references.index', [
            ...$query,
            'expression' => $expression->id,
        ]);
    }

    /**
     * Get the references in the given expression.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     *
     * @return MultiplePendingTurboStreamResponse|RedirectResponse
     */
    public function index(Request $request, WorkExpression $expression): MultiplePendingTurboStreamResponse|RedirectResponse
    {
        $task = $this->task();

        /** @var string|null $activate */
        $activate = request('activate');

        if ($activate && $redirect = $this->locateAndNavigate($expression, $activate)) {
            return $redirect;
        }

        $isPreview = $this->isPreviewing($expression->id);

        $meta = $this->getVisibleMeta();

        $categoryTypes = CategoryType::pluck('colour', 'id');

        $requestFilters = collect($this->getFiltersFromRequest($request));

        $filters = $this->getReferenceFilters();

        $filters = $requestFilters->isEmpty() ? $filters : $requestFilters;

        $multi = $filters->filter(fn ($val, $key) => in_array($key, $this->multiFilters));
        $filters = $filters->filter(fn ($val, $key) => !in_array($key, $this->multiFilters));

        $fields = $this->referenceMetaFields($request);
        $withDrafts = [];
        foreach ($meta as $key => $value) {
            $withDrafts[] = $fields['metaDrafts'][$key];
        }

        $fromBulk = collect(session('bulk-references', []))->reverse()->slice(0, $this->perPage);

        $referenceQuery = $this->baseQuery($expression)
            ->with($this->getMetaValues($meta))
            ->with($withDrafts)
            ->with([
                'summary',
                'summaryDraft',
                'refSelector:reference_id,selectors',
                'htmlContent:reference_id,cached_content',
                'refRequirement:reference_id',
                'requirementDraft:reference_id,change_status',
                'refPlainText:reference_id,plain_text',
            ])
            ->when($task, fn ($builder) => $builder->withCount(['collaborateComments']));

        if ($fromBulk->isNotEmpty()) {
            $references = $referenceQuery->whereKey($fromBulk->toArray())
                ->get()
                ->map(function ($ref) use ($expression, $task, $categoryTypes, $fields) {
                    /** @var Reference $ref */
                    return $this->referenceToTurbo($ref, $expression, false, $task, $categoryTypes, $fields);
                })
                ->toArray();

            return multipleTurboStreamResponse($references);
        }

        $referenceQuery->filter($multi->toArray())
            ->where(function ($builder) use ($filters) {
                $filters->each(function ($value, $key) use ($builder) {
                    $builder->orWhere(fn ($query) => $query->filter([$key => $value]));
                });
            });

        $references = $referenceQuery->paginate($this->perPage);

        return multipleTurboStreamResponse([
            singleTurboStreamResponse('work_expression_references', 'update')
                ->view('pages.corpus.collaborate.annotations.partials.references', [
                    'expression' => $expression,
                    'references' => $references,
                    'active' => $activate,
                    'task' => $task,
                    'categoryTypes' => $categoryTypes,
                    'previewing' => $isPreview,
                    ...$fields,
                ]),
            singleTurboStreamResponse('meta_togglers', 'update')
                ->view('pages.corpus.collaborate.annotations.partials.metadata-toggle', [
                    'expression' => $expression,
                    'references' => $references,
                    'task' => $task,
                    'hasNotes' => $this->workHasNotes($expression),
                ]),
        ]);
    }

    /**
     * Locate the reference and redirect if required.
     *
     * @param WorkExpression $expression
     * @param int|string     $reference
     *
     * @return RedirectResponse|null
     */
    protected function locateAndNavigate(WorkExpression $expression, int|string $reference): ?RedirectResponse
    {
        if ($this->getReferenceFilters()->isNotEmpty()) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        /** @var Collection<int> $references */
        $references = $this->baseQuery($expression)->pluck('id');
        /** @var int|false $position */
        $position = $references->search($reference);

        if ($position === false) {
            return null;
        }

        /** @var string|int $page */
        $page = request('page', 1);
        $volume = ceil(($position + 1) / $this->perPage);

        if (((float) $page) === $volume) {
            return null;
        }

        return redirect()->route('collaborate.work-expressions.references.index', [
            'expression' => $expression->id,
            'page' => (int) $volume,
            'activate' => $reference,
        ]);
    }

    /**
     * Generate a turbo response.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     * @param bool           $active
     * @param Task|null      $task
     *
     * @return PendingTurboStreamResponse
     */
    protected function toTurbo(WorkExpression $expression, Reference $reference, bool $active, ?Task $task): PendingTurboStreamResponse
    {
        $categoryTypes = CategoryType::pluck('colour', 'id');

        /** @var Request $request */
        $request = request();

        $fields = $this->referenceMetaFields($request);

        return $this->referenceToTurbo($reference, $expression, $active, $task, $categoryTypes, $fields);
    }

    /**
     * Set the current reference as active or inactive.
     *
     * @param WorkExpression $expression
     * @param int            $reference
     * @param bool           $active
     *
     * @return MultiplePendingTurboStreamResponse|RedirectResponse
     */
    protected function toggleActive(WorkExpression $expression, int $reference, bool $active): MultiplePendingTurboStreamResponse|RedirectResponse
    {
        $task = $this->task();

        /** @var string|null $previous */
        $previous = session('collaborate_active_reference');
        session()->put('collaborate_active_reference', $active ? $reference : null);

        if ($active && $redirect = $this->locateAndNavigate($expression, $reference)) {
            // @codeCoverageIgnoreStart
            return $redirect;
            // @codeCoverageIgnoreEnd
        }

        /** @var Reference|null $previous */
        $previous = $previous && $previous != $reference
            ? $this->baseQuery($expression)
                ->when($task, fn ($builder) => $builder->withCount(['collaborateComments']))
                ->with([
                    'refSelector:reference_id,selectors',
                    'htmlContent:reference_id,cached_content',
                    'refRequirement:reference_id',
                    'requirementDraft:reference_id,change_status',
                    'refPlainText:reference_id,plain_text',
                ])
                ->find($previous)
            // @codeCoverageIgnoreStart
            : null;
        // @codeCoverageIgnoreEnd

        $meta = self::getVisibleMeta();

        /** @var Reference $reference */
        $reference = Reference::whereKey($reference)
            ->withCount(['linkedParents', 'linkedChildren'])
            ->with($this->getMetaValues($meta))
            ->with([
                'refSelector:reference_id,selectors',
                'htmlContent:reference_id,cached_content',
                'refRequirement:reference_id',
                'requirementDraft:reference_id,change_status',
                'refPlainText:reference_id,plain_text',
            ])
            ->when($task, fn ($builder) => $builder->withCount(['collaborateComments']))
            ->when($active, fn ($builder) => $builder->with([
                'linkedParents.refPlainText',
                'linkedChildren.refPlainText',
                'linkedParents.work:id,title',
                'linkedChildren.work:id,title',
                'consequences',
            ]))
            ->findOrFail($reference);

        $response = [
            $this->toTurbo($expression, $reference, $active, $task),
        ];

        if ($previous) {
            $response[] = $this->toTurbo($expression, $previous, false, $task);
        }

        return multipleTurboStreamResponse($response);
    }

    /**
     * Activate the active reference.
     *
     * @param WorkExpression $expression
     * @param int            $reference
     *
     * @return MultiplePendingTurboStreamResponse|RedirectResponse
     */
    public function activate(WorkExpression $expression, int $reference): MultiplePendingTurboStreamResponse|RedirectResponse
    {
        return $this->toggleActive($expression, $reference, true);
    }

    /**
     * Deactivate the active reference.
     *
     * @param WorkExpression $expression
     * @param int            $reference
     *
     * @return MultiplePendingTurboStreamResponse|RedirectResponse
     */
    public function deactivate(WorkExpression $expression, int $reference): MultiplePendingTurboStreamResponse|RedirectResponse
    {
        return $this->toggleActive($expression, $reference, false);
    }

    /**
     * Get the ToC Items.
     *
     * @param WorkExpression $expression
     *
     * @return PendingTurboStreamResponse
     */
    public function toc(WorkExpression $expression): PendingTurboStreamResponse
    {
        $references = Reference::where('work_id', $expression->work_id)
            ->whereIn('type', [
                ReferenceType::work()->value,
                ReferenceType::citation()->value,
            ])
            ->orderBy('volume')
            ->orderBy('start')
            ->with([
                'refSelector:reference_id,selectors',
                'htmlContent:reference_id,cached_content',
                'refTitleText:reference_id,text',
                'refPlainText:reference_id,plain_text',
            ])
            ->select(['id', 'level', 'volume'])
            ->paginate(100, pageName: 'toc-page');

        return singleTurboStreamResponse('work_expression_toc')
            ->view('pages.corpus.collaborate.annotations.partials.toc', [
                'references' => $references,
                'expression' => $expression,
            ]);
    }

    /**
     * @param Reference             $reference
     * @param WorkExpression        $expression
     * @param bool                  $active
     * @param Task|null             $task
     * @param Collection            $categoryTypes
     * @param array<string, string> $fields
     *
     * @return mixed
     */
    protected function referenceToTurbo(Reference $reference, WorkExpression $expression, bool $active, ?Task $task, Collection $categoryTypes, array $fields)
    {
        return singleTurboStreamResponse("reference_{$reference->id}", 'replace')
            ->view('pages.corpus.collaborate.annotations.partials.reference-card', [
                'expression' => $expression,
                'reference' => $reference,
                'active' => $active,
                'task' => $task,
                'categoryTypes' => $categoryTypes,
                'previewing' => $this->isPreviewing($expression->id),
                ...$fields,
            ]);
    }
}
