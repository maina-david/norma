<?php

namespace App\Http\Controllers\Api\Internals\Collaborate\Corpus;

use App\Enums\Assess\AssessmentItemType;
use App\Enums\Corpus\ReferenceType;
use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Resources\Internals\Collaborate\Corpus\ReferenceResource;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Requirements\ReferenceSummaryDraft;
use App\Models\Requirements\Summary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ReferenceController extends AbstractApiController
{
    /** @var string[] */
    protected array $multiFilters = [
        'assessmentItemsWithDrafts', 'topicsWithDrafts', 'questionsWithDrafts', 'tagsWithDrafts',
        'locationsWithDrafts', 'domainsWithDrafts', 'actionAreasWithDrafts',
    ];

    /**
     * {@inheritDoc}
     */
    protected function getAllowedIncludes(): array
    {
        return [
            'actionAreas',
            'actionAreaDrafts',
            'assessmentItems',
            'assessmentItemDrafts',
            'categories',
            'categoryDrafts',
            'categories.categoryType',
            'categoryDrafts.categoryType',
            'contextQuestions',
            'contextQuestionDrafts',
            'legalDomains',
            'legalDomainDrafts',
            'locations',
            'locationDrafts',
            'tags',
            'tagDrafts',
            'latestTocItem',
        ];
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
     * Get the custom includes queries.
     *
     * @return array<string, mixed>
     */
    protected function getCustomIncludesQueries(): array
    {
        return [
            'assessmentItems' => fn ($query) => $query->where('type', AssessmentItemType::LEGACY->value),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getQuery(Request $request): Builder
    {
        $workId = null;
        $hasDoc = false;

        if ($request->route('work')) {
            /** @var Work $work */
            $work = Work::with(['activeExpression'])->findOrFail($request->route('work'));
            $workId = $work->id;
            $hasDoc = $work->docs()->exists();
        }

        if ($request->route('expression')) {
            /** @var WorkExpression $expression */
            $expression = WorkExpression::findOrFail($request->route('expression'));
            $workId = $expression->work_id;
            $hasDoc = (bool) $expression->doc_id;
        }

        $isIndex = $request->routeIs('api.collaborate.work-expression.references.task.index')
            || $request->routeIs('api.collaborate.works.references.index');

        abort_if(!$workId, 404);

        $query = parent::getQuery($request)
            ->whereIn('type', [
                ReferenceType::work()->value,
                ReferenceType::citation()->value,
            ])
            ->with([
                'refPlainText',
                'refSelector:reference_id,selectors',
                'latestTocItem.parent.parent.parent',
                'contentDraft:reference_id,title',
            ])
            ->withCount([
                'linkedChildren',
                'linkedParents',
                'refRequirement',
                'requirementDraft',
                'collaborateComments',
            ])
            ->addSelect([
                'summary_count' => Summary::whereColumn('reference_id', (new Reference())->qualifyColumn('id'))
                    ->selectRaw('LENGTH(COALESCE(summary_body, ""))'),
                'summary_draft_count' => ReferenceSummaryDraft::whereColumn('reference_id', (new Reference())->qualifyColumn('id'))
                    ->selectRaw('LENGTH(COALESCE(summary_body, ""))'),
            ])
            ->where('work_id', $workId)
            ->when($request->routeIs('api.collaborate.work-expression.references.task.show'), fn ($q) => $q->with(['requirementDraft'])->withCount(['contentExtracts']))
            ->when($isIndex, function ($query) use ($request) {
                $filters = collect($this->getFiltersFromRequest($request));

                $multi = $filters->filter(fn ($val, $key) => in_array($key, $this->multiFilters));
                $filters = $filters->filter(fn ($val, $key) => !in_array($key, $this->multiFilters));

                $query
                    ->filter($multi->toArray())
                    ->where(function ($builder) use ($filters) {
                        $filters->each(function ($value, $key) use ($builder) {
                            $builder->orWhere(fn ($query) => $query->filter([$key => $value]));
                        });
                    })
                    ->when($request->has('search'), fn ($builder) => $builder->filter($request->only('search')));
            });

        $query->reorder();

        return $query->when($hasDoc, fn ($query) => $query->orderBy('position'))
            ->when(!$hasDoc, fn ($query) => $query->orderBy('volume')->orderBy('start'));
    }

    /**
     * {@inheritDoc}
     */
    protected function getModelClass(): string
    {
        return Reference::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getApiResourceClass(): string
    {
        return ReferenceResource::class;
    }

    /**
     * {@inheritDoc}
     */
    public function show(Request $request, string $id, ?Builder $query = null)
    {
        /** @var string $reference */
        $reference = $request->route('reference');

        if (!$query) {
            $query = $this->getQuery($request)
                ->with([
                    'summary',
                    'summaryDraft',
                    'linkedParents.contentDraft:reference_id,title',
                    'linkedParents.work:id,title',
                    'linkedParents.refPlainText',
                    'linkedParents.refSelector:reference_id,selectors',
                    'linkedChildren.contentDraft:reference_id,title',
                    'linkedChildren.work:id,title',
                    'linkedChildren.refPlainText',
                    'linkedChildren.refSelector:reference_id,selectors',
                    'refRequirement',
                    'requirementDraft',
                ]);
        }

        return parent::show($request, $reference, $query);
    }

    /**
     * Get the ToC Items.
     *
     * @param WorkExpression $expression
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection<ReferenceResource>
     */
    public function toc(WorkExpression $expression): AnonymousResourceCollection
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
                'refTitleText:reference_id,text',
                'refPlainText:reference_id,plain_text',
            ])
            ->select(['id', 'level', 'volume'])
            ->paginate(100);

        return ReferenceResource::collection($references);
    }

    /**
     * {@inheritDoc}
     */
    public function index(Request $request, ?Builder $query = null): ResourceCollection
    {
        /** @var string|null $activate */
        $activate = $request->query('activate');

        $page = $activate ? $this->locateAndNavigate($request, $activate) : null;

        $query ??= $this->getQuery($request);

        $results = $this->processListingQuery($request, $query, $page);

        return ReferenceResource::collection($results)->preserveQuery();
    }

    /**
     * Locate the reference and redirect if required.
     *
     * @param \Illuminate\Http\Request $request
     * @param int|string               $reference
     *
     * @return int|null
     */
    protected function locateAndNavigate(Request $request, int|string $reference): ?int
    {
        /** @var \Illuminate\Support\Collection<int> $references */
        $references = $this->getQuery($request)
            ->apiQueryFilter($request)
            ->pluck('id');

        /** @var int|false $position */
        $position = $references->search($reference);

        if ($position === false) {
            /** @var int */
            return $request->get('page', 1);
        }

        $volume = ceil(($position + 1) / $this->perPage);

        return (int) $volume;
    }
}
