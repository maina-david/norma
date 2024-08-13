<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Actions\Corpus\Reference\BulkApplyTaggableDrafts;
use App\Actions\Corpus\Reference\BulkAttachTaggables;
use App\Actions\Corpus\Reference\BulkRemoveAllTaggableDrafts;
use App\Actions\Corpus\Reference\BulkRemoveAllTaggables;
use App\Actions\Corpus\Reference\BulkRemoveTaggableDrafts;
use App\Actions\Corpus\Reference\BulkRemoveTaggables;
use App\Enums\Corpus\ReferenceType;
use App\Http\Controllers\Traits\UsesAnnotationPage;
use App\Http\Controllers\Traits\UsesMetaDataPivotClasses;
use App\Http\Requests\Corpus\ReferenceMetaRequest;
use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use App\Models\Ontology\CategoryType;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ReferenceMetadataController extends WorkflowTaskController
{
    use UsesAnnotationPage;
    use UsesMetaDataPivotClasses;

    /**
     * Get the metadata items that should be shown.
     *
     * @param Request $request
     *
     * @return Collection<string, string>
     */
    protected function getVisibleMetaFromRequest(Request $request): Collection
    {
        $valid = [
            'assessments' => 'assessmentItems',
            'topics' => 'categories',
            'context' => 'contextQuestions',
            'domains' => 'legalDomains',
            'jurisdictions' => 'locations',
            'tags' => 'tags',
        ];

        /** @var string $meta */
        $meta = $request->query('show', '');

        /** @var Collection<string, string> */
        $meta = collect(explode('|', $meta))
            ->filter(fn ($item) => array_key_exists($item, $valid))
            ->mapWithKeys(fn ($item) => [$item => $valid[$item]]);

        $this->setVisibleMeta($meta->toArray());

        return $meta;
    }

    /**
     * Get the turbo stream for the given frame.
     *
     * @param WorkExpression $expression
     *
     * @return PendingTurboStreamResponse
     */
    public function turboFrame(WorkExpression $expression): PendingTurboStreamResponse
    {
        $this->abortNotTurbo();
        $task = $this->task();

        return singleTurboStreamResponse('meta_togglers', 'update')
            ->view('pages.corpus.collaborate.annotations.partials.metadata-toggle', [
                'expression' => $expression,
                'task' => $task,
                'hasNotes' => $this->workHasNotes($expression),
            ]);
    }

    /**
     * Get the references collection for the turbo stream.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     * @param Collection     $references
     *
     * @return Collection
     */
    protected function referenceCardMetaTurboStream(Request $request, WorkExpression $expression, Collection $references): Collection
    {
        $this->abortNotTurbo();

        $categoryTypes = CategoryType::pluck('colour', 'id');

        $fields = $this->referenceMetaFields($request);

        return $references->map(
            fn ($reference) => singleTurboStreamResponse("meta_{$reference->id}_container", 'update')
                ->view('pages.corpus.collaborate.annotations.partials.reference-card-meta', [
                    'expression' => $expression,
                    'reference' => $reference,
                    'categoryTypes' => $categoryTypes,
                    ...$fields,
                ])
        );
    }

    /**
     * Get the references meta in the given expression.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     *
     * @return MultiplePendingTurboStreamResponse|RedirectResponse
     */
    public function index(Request $request, WorkExpression $expression): MultiplePendingTurboStreamResponse|RedirectResponse
    {
        $this->abortNotTurbo();
        $filters = $this->getReferenceFilters();

        $meta = $this->getVisibleMetaFromRequest($request);
        $fields = $this->referenceMetaFields($request);
        $withDrafts = [];
        foreach ($meta as $key => $value) {
            $withDrafts[] = $fields['metaDrafts'][$key];
        }

        $references = $this->baseReferenceQuery($expression)
            ->select(['id'])
            ->with($this->getMetaValues($meta))
            ->with($withDrafts)
            ->where(function ($builder) use ($filters) {
                $filters->each(function ($value, $key) use ($builder) {
                    // @codeCoverageIgnoreStart
                    $builder->orWhere(fn ($query) => $query->filter([$key => $value]));
                    // @codeCoverageIgnoreEnd
                });
            })
            ->paginate($this->perPage)
            ->items();

        $references = $this->referenceCardMetaTurboStream($request, $expression, collect($references));
        $references->push($this->turboFrame($expression));

        return multipleTurboStreamResponse($references->toArray());
    }

    /**
     * Get the form.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     * @param string         $relation
     *
     * @throws AuthorizationException
     *
     * @return PendingTurboStreamResponse
     */
    public function create(WorkExpression $expression, Reference $reference, string $relation): PendingTurboStreamResponse
    {
        $this->abortNotTurbo();
        $expression->load(['work']);

        $permission = Str::kebab($relation);

        $this->authorize("collaborate.corpus.work-expression.attach-{$permission}");
        $reference->load(['categories']);

        /** @var Collection $categories */
        $categories = $reference->categories;
        $categories = $categories->map(fn ($item) => $item->id)->toArray();

        return singleTurboStreamResponse("meta_{$reference->id}_attach", 'update')
            ->view('partials.corpus.reference.collaborate.add-meta', [
                'expression' => $expression,
                'reference' => $reference,
                'relation' => $relation,
                'location' => $expression->work->primary_location_id,
                'categories' => $categories,
            ]);
    }

    /**
     * Store the new relation.
     *
     * @param ReferenceMetaRequest $request
     * @param WorkExpression       $expression
     * @param string               $reference
     * @param string               $relation
     *
     * @throws AuthorizationException
     *
     * @return RedirectResponse
     */
    public function store(ReferenceMetaRequest $request, WorkExpression $expression, string $reference, string $relation): RedirectResponse|MultiplePendingTurboStreamResponse
    {
        $this->abortNotTurbo();

        $permission = Str::kebab($relation);
        $this->authorize("collaborate.corpus.work-expression.attach-{$permission}");

        if ($reference === 'bulk') {
            $this->authorize('collaborate.corpus.work-expression.use-bulk-actions');

            return $this->storeForBulk($expression, $request, $relation);
        }

        /** @var Reference $reference */
        $reference = Reference::findOrFail($reference);
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        app(BulkAttachTaggables::class)->handle(
            [$reference->id],
            $request->collect('related')->toArray(),
            $this->getMetaPivotClass($relation),
            $this->getMetaDraftPivotClass($relation),
            $user
        );

        $this->notifyGeneralSuccess();

        return redirect()->route('collaborate.corpus.references.meta.show', [
            'expression' => $expression->id,
            'reference' => $reference->id,
            'children' => in_array($relation, ['legalDomains', 'locations']),
        ]);
    }

    /**
     * Store for bulk.
     *
     * @param WorkExpression       $expression
     * @param ReferenceMetaRequest $request
     * @param string               $relation
     *
     * @return MultiplePendingTurboStreamResponse
     */
    public function storeForBulk(WorkExpression $expression, ReferenceMetaRequest $request, string $relation): MultiplePendingTurboStreamResponse
    {
        $references = $request->collect()
            ->filter(fn ($val, $key) => $val != 'false' && str_starts_with($key, 'reference_'))
            ->map(fn ($val, $key) => (int) substr($key, 10))
            ->values();

        $related = $request->collect('related');
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        app(BulkAttachTaggables::class)->handle(
            $references->toArray(),
            $related->toArray(),
            $this->getMetaPivotClass($relation),
            $this->getMetaDraftPivotClass($relation),
            $user
        );

        $this->notifyGeneralSuccess();

        return $this->postBulkAction($request, $expression, $references->toArray());
    }

    /**
     * Show the metadata.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     * @param Reference      $reference
     *
     * @return MultiplePendingTurboStreamResponse
     */
    public function show(Request $request, WorkExpression $expression, Reference $reference): MultiplePendingTurboStreamResponse
    {
        $this->abortNotTurbo();

        $fields = $this->referenceMetaFields($request);

        $meta = $this->getVisibleMeta();

        $reference->load($fields['meta']->values()->toArray());
        $reference->load($fields['metaDrafts']->values()->toArray());

        $categoryTypes = CategoryType::pluck('colour', 'id');
        $responses = [
            singleTurboStreamResponse("meta_{$reference->id}", 'replace')
                ->view('pages.corpus.collaborate.annotations.partials.reference-card-meta', [
                    'expression' => $expression,
                    'reference' => $reference,
                    'categoryTypes' => $categoryTypes,
                    ...$fields,
                ]),
        ];

        $responses[] = $this->template($expression);

        if (!request('children')) {
            return multipleTurboStreamResponse($responses);
        }

        $children = Reference::where('work_id', $reference->work_id)
            ->where('volume', $reference->volume)
            ->when($reference->level, fn ($builder) => $builder->where('level', '>', $reference->level))
            ->when($reference->start, fn ($builder) => $builder->where('start', '>=', $reference->start))
            ->where('type', ReferenceType::citation()->value)
            ->with($this->getMetaValues($meta))
            ->with($fields['metaDrafts']->values()->toArray())
            ->orderBy('start')
            ->take($this->perPage)
            ->get(['id']);

        foreach ($children as $child) {
            $responses[] = singleTurboStreamResponse("meta_{$child->id}", 'replace')
                ->view('pages.corpus.collaborate.annotations.partials.reference-card-meta', [
                    'expression' => $expression,
                    'reference' => $child,
                    'categoryTypes' => $categoryTypes,
                    ...$fields,
                ]);
        }

        return multipleTurboStreamResponse($responses);
    }

    /**
     * Remove a taggable.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     * @param string         $relation
     * @param int|string     $related
     *
     * @throws AuthorizationException
     *
     * @return RedirectResponse
     */
    public function destroy(WorkExpression $expression, Reference $reference, string $relation, int|string $related): RedirectResponse
    {
        $this->abortNotTurbo();

        $permission = Str::kebab($relation);

        $this->authorize("collaborate.corpus.work-expression.detach-{$permission}");

        $relatedItems = $related === 'all' ? null : $related;

        $related = $reference->{$relation}();
        $related->when($relatedItems, fn ($query) => $query->whereKey($relatedItems));
        $related = $related->get();

        if (!is_null($relatedItems)) {
            app(BulkRemoveTaggables::class)->handle(
                [$reference->id],
                [(int) $relatedItems],
                $this->getMetaPivotClass($relation),
                $this->getMetaDraftPivotClass($relation),
                request()->user(),
            );
        } else {
            app(BulkRemoveAllTaggables::class)->handle(
                [$reference->id],
                $this->getMetaPivotClass($relation),
                $this->getMetaDraftPivotClass($relation),
                request()->user(),
            );
        }

        return redirect()->route('collaborate.corpus.references.meta.show', [
            'expression' => $expression->id,
            'reference' => $reference->id,
            'children' => in_array($relation, ['legalDomains', 'locations']),
        ]);
    }

    /**
     * Remove a taggable draft.
     *
     * @param WorkExpression $expression
     * @param Reference      $reference
     * @param string         $relation
     * @param int            $related
     *
     * @throws AuthorizationException
     *
     * @return RedirectResponse
     */
    public function destroyDraft(WorkExpression $expression, Reference $reference, string $relation, int $related): RedirectResponse
    {
        $this->abortNotTurbo();

        $permission = Str::kebab($relation);

        $this->authorize("collaborate.corpus.work-expression.detach-{$permission}");

        app(BulkRemoveTaggableDrafts::class)->handle(
            [$reference->id],
            [$related],
            $this->getMetaDraftPivotClass($relation),
        );

        return redirect()->route('collaborate.corpus.references.meta.show', [
            'expression' => $expression->id,
            'reference' => $reference->id,
            'children' => in_array($relation, ['legalDomains', 'locations']),
        ]);
    }

    /**
     * Get the bulk actions form.
     *
     * @param WorkExpression $expression
     * @param Request        $request
     *
     * @return PendingTurboStreamResponse
     */
    public function bulkActionsForm(WorkExpression $expression, Request $request): PendingTurboStreamResponse
    {
        $this->abortNotTurbo();
        $expression->load(['work']);

        return singleTurboStreamResponse($request->header('turbo-frame') ?? '', 'replace')
            ->view('partials.corpus.reference.collaborate.add-meta', [
                'expression' => $expression,
                'noClose' => true,
                'reference' => (object) ['id' => 'bulk'],
                'relation' => 'assessmentItems',
                'location' => $expression->work->primary_location_id,
                'isBulkAction' => true,
            ]);
    }

    /**
     * Get the bulk actions form.
     *
     * @codeCoverageIgnore
     *
     * @param WorkExpression $expression
     *
     * @return PendingTurboStreamResponse
     */
    public function template(WorkExpression $expression): PendingTurboStreamResponse
    {
        $this->abortNotTurbo();
        $expression->load(['work']);

        return singleTurboStreamResponse('annotation-meta-template', 'update')
            ->view('partials.corpus.reference.collaborate.add-meta', [
                'expression' => $expression,
                'noClose' => false,
                'reference' => (object) ['id' => 'met_temp'],
                'relation' => 'assessmentItems',
                'location' => $expression->work->primary_location_id,
                'isBulkAction' => false,
            ]);
    }

    /**
     * Bulk detach.
     *
     * @param ReferenceMetaRequest $request
     * @param WorkExpression       $expression
     * @param string               $relation
     *
     * @return MultiplePendingTurboStreamResponse
     */
    public function destroyForBulk(ReferenceMetaRequest $request, WorkExpression $expression, string $relation): MultiplePendingTurboStreamResponse
    {
        $this->abortNotTurbo();

        $references = $request->collect()
            ->filter(fn ($val, $key) => $val != 'false' && str_starts_with($key, 'reference_'))
            ->map(fn ($val, $key) => (int) substr($key, 10))
            ->values();

        $related = $request->collect('related');

        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        if ($related->isNotEmpty()) {
            app(BulkRemoveTaggables::class)->handle(
                $references->toArray(),
                $related->toArray(),
                $this->getMetaPivotClass($relation),
                $this->getMetaDraftPivotClass($relation),
                $user
            );
        }

        if ($request->get('_delete_target') === 'all') {
            app(BulkRemoveAllTaggables::class)->handle(
                $references->toArray(),
                $this->getMetaPivotClass($relation),
                $this->getMetaDraftPivotClass($relation),
                $user
            );
        }

        $this->notifySuccessfulUpdate();

        return $this->postBulkAction($request, $expression, $references->toArray());
    }

    /**
     * Bulk delete drafts.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     * @param string         $relation
     *
     * @return MultiplePendingTurboStreamResponse
     */
    public function deleteDraftsForBulk(Request $request, WorkExpression $expression, string $relation): MultiplePendingTurboStreamResponse
    {
        $this->abortNotTurbo();

        $references = $request->collect()
            ->filter(fn ($val, $key) => $val != 'false' && str_starts_with($key, 'reference_'))
            ->map(fn ($val, $key) => (int) substr($key, 10))
            ->values();

        app(BulkRemoveAllTaggableDrafts::class)->handle(
            $references->toArray(),
            $this->getMetaDraftPivotClass($relation),
        );

        $this->notifySuccessfulUpdate();

        return $this->postBulkAction($request, $expression, $references->toArray());
    }

    /**
     * Bulk delete drafts.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     * @param string         $relation
     *
     * @return MultiplePendingTurboStreamResponse
     */
    public function applyDraftsForBulk(Request $request, WorkExpression $expression, string $relation): MultiplePendingTurboStreamResponse
    {
        $this->abortNotTurbo();

        $references = $request->collect()
            ->filter(fn ($val, $key) => $val != 'false' && str_starts_with($key, 'reference_'))
            ->map(fn ($val, $key) => (int) substr($key, 10))
            ->values();

        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        app(BulkApplyTaggableDrafts::class)->handle(
            $references->toArray(),
            $this->getMetaPivotClass($relation),
            $this->getMetaDraftPivotClass($relation),
            $user
        );

        $this->notifySuccessfulUpdate();

        return $this->postBulkAction($request, $expression, $references->toArray());
    }

    /**
     * Return only the changed references after a bulk action.
     *
     * @param Request               $request
     * @param WorkExpression        $expression
     * @param array<array-key, int> $references
     *
     * @return MultiplePendingTurboStreamResponse
     */
    public function postBulkAction(Request $request, WorkExpression $expression, array $references): MultiplePendingTurboStreamResponse
    {
        $this->abortNotTurbo();

        $meta = $this->getVisibleMeta();
        $fields = $this->referenceMetaFields($request);

        $references = $this->baseReferenceQuery($expression)
            ->select(['id'])
            ->with($this->getMetaValues($meta))
            ->with($fields['metaDrafts']->values()->toArray())
            ->whereKey($references)
            ->get();

        $references = $this->referenceCardMetaTurboStream($request, $expression, collect($references));
        $references->push($this->turboFrame($expression));

        return multipleTurboStreamResponse($references->toArray());
    }
}
