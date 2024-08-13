<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Enums\Corpus\WorkStatus;
use App\Enums\Corpus\WorkType;
use App\Enums\Workflows\CollaborateSessionKey;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Corpus\WorkRequest;
use App\Models\Assess\AssessmentItem;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Work;
use App\Models\Ontology\Category;
use App\Models\Ontology\Tag;
use App\Models\Workflows\Task;
use App\Traits\Arachno\UsesSourceFilter;
use App\Traits\Geonames\UsesJurisdictionFilter;
use App\Traits\Ontology\UsesLegalDomainFilter;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\ComponentAttributeBag;
use ReflectionException;
use Symfony\Component\HttpFoundation\Response;

use function HotwiredLaravel\TurboLaravel\dom_id;

class WorkController extends CollaborateController
{
    use UsesJurisdictionFilter;
    use UsesLegalDomainFilter;
    use UsesSourceFilter;

    /** @var string */
    protected string $sortBy = 'title';

    /** @var bool */
    protected bool $withCreate = false;

    /** @var bool */
    protected bool $withDelete = false;

    /**
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)
            ->with(['primaryLocation'])
            ->when($request->routeIs('collaborate.works.edit'), fn ($q) => $q->with(['organisation']))
            ->when($request->routeIs('collaborate.works.show'), fn ($q) => $q->with([
                'maintainingUpdates',
                'organisation',
                'maintainingDocs:id,title',
                'maintainingDocs.docMeta:doc_id,title_translation',
            ]));
    }

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @phpstan-return class-string
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Work::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.works';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return WorkRequest::class;
    }

    /**
     * Get the model columns that should be displayed in the index table.
     *
     * Use the dot notation for relations and for custom results use functions that
     * will receive the current row as an arg. The field name will be used as the
     * translation key but when you use functions, make sure the array is keyed with the
     * translation name.
     *
     * e.g. ['profile' => 'profile.id', 'name'].
     *
     * @return array<string|int, mixed>
     */
    protected static function indexColumns(): array
    {
        $request = app(Request::class);
        /** @var array<string, mixed> */
        $filters = $request->query();

        $filters = Arr::only($filters, [
            'assessmentItems',
            'topics',
            'questions',
            'domains',
            'tags',
        ]);

        return [
            'title' => fn ($row) => self::renderPartial($row, 'title-column'),
            'annotate' => fn ($row) => self::renderPartial($row, 'annotate-column', ['filters' => $filters]),
            'annotate-preview' => fn ($row) => self::renderPartial($row, 'annotate-preview-column', ['filters' => $filters]),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'headings' => false,
        ];
    }

    /**
     * Get the resource filters.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function resourceFilters(): array
    {
        return [
            'jurisdiction' => $this->getJurisdictionFilter(),
            'domains' => $this->getLegalDomainFilter(),
            'workType' => [
                'label' => __('corpus.work.type'),
                'render' => fn () => view('components.corpus.work.work-type-selector', [
                    'value' => $this->getFilterValue('workType'),
                    'name' => 'workType',
                    'attributes' => new ComponentAttributeBag([]),
                ]),
                'value' => fn ($value) => WorkType::lang()[WorkType::fromValue($value)->value],
            ],
            'status' => [
                'label' => __('corpus.work.status'),
                'render' => fn () => view('components.corpus.work.work-status-selector', [
                    'value' => $this->getFilterValue('status'),
                    'name' => 'status',
                    'attributes' => new ComponentAttributeBag([]),
                ]),
                'value' => fn ($value) => WorkStatus::lang()[WorkStatus::fromValue((int) $value)->value],
            ],
            'source' => $this->getSourceFilter(),
            'questions' => [
                'label' => __('compilation.context_question.question'),
                'render' => null,
                'value' => fn ($value) => ContextQuestion::find($value)?->toQuestion() ?? '', // @phpstan-ignore-line
            ],
            'assessment_items' => [
                'label' => __('assess.assessment_item.sai'),
                'render' => null,
                'value' => fn ($value) => AssessmentItem::find($value)?->toDescription() ?? '', // @phpstan-ignore-line
            ],
            'tags' => [
                'label' => __('ontology.tag.tags'),
                'render' => null,
                'value' => fn ($value) => Tag::find($value)?->title ?? '',
            ],
            'topics' => [
                'label' => __('ontology.category.topics'),
                'render' => null,
                'value' => fn ($value) => Category::find($value)?->display_label ?? '',
            ],
        ];
    }

    /**
     * Get base resource route parameters that are required to create the route.
     *
     * @return array<string, mixed>
     */
    protected function resourceRouteParams(): array
    {
        /** @var Request $request */
        $request = request();

        return [
            'task' => $request->route('task', $request->get('task')),
        ];
    }

    /**
     * Get the task data.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    protected function getTaskData(Request $request): array
    {
        /** @var int|null $task */
        $task = $request->route('task');

        if ($task) {
            $task = Task::whereRelation('document.expression', 'work_id', $request->route('work'))
                ->whereKey($task)
                ->with(['document'])
                ->firstOrFail();

            $this->authorize('view', $task);
            session()->put(CollaborateSessionKey::CURRENT_TASK->value, $task);

            return [
                'baseRoute' => 'collaborate.works.task',
                'bottomHeader' => 'pages.corpus.collaborate.work.partials.task-header',
                'task' => $task,
            ];
        }

        return [];
    }

    /**
     * @param Request $request
     *
     * @return mixed[]
     */
    protected function editViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-7xl',
            ...$this->getTaskData($request),
        ];
    }

    /**
     * @param Request $request
     *
     * @return mixed[]
     */
    protected function showViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-7xl',
            ...$this->getTaskData($request),
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @param int     $resourceId
     *
     * @throws AuthorizationException
     *
     * @return View|Response
     */
    public function show(Request $request, int $resourceId): View|Response
    {
        if (!$request->isForTurboFrame()) {
            /** @var View */
            return parent::show($request, $resourceId);
        }

        /** @var Work $resource */
        $resource = $this->baseQuery($request)
            ->with('catalogueDoc')
            ->findOrFail($resourceId);

        if (method_exists($this, 'authoriseAction')) {
            $this->authoriseAction('show', $resource);
        }

        return singleTurboStreamResponse(dom_id($resource))
            ->view('pages.corpus.collaborate.work.partials.work-details', [
                'resource' => $resource,
            ])
            ->toResponse($request);
    }

    /**
     * Which route to redirect to after update action.
     *
     * @param Work $model
     *
     * @throws ReflectionException
     *
     * @return string
     */
    protected function redirectAfterUpdate(Model $model): string
    {
        /** @var Request $request */
        $request = request();

        if (!$request->route('task')) {
            return parent::redirectAfterUpdate($model);
        }

        // @codeCoverageIgnoreStart
        return route('collaborate.works.task.show', ['work' => $model->id, 'task' => $request->route('task')]);
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param Request $request
     * @param Work    $work
     *
     * @return RedirectResponse
     */
    public function linkToDoc(Request $request, Work $work): RedirectResponse
    {
        $docId = $request->input('catalogue_doc_id');

        /** @var CatalogueDoc $catDoc */
        $catDoc = CatalogueDoc::with(['latestDoc'])->findOrFail($docId);

        if (Work::where('uid', $catDoc->uid)->exists()) {
            $this->notifyErrorMessage('There is another work linked to that doc already');

            return back();
        }

        $work->update(['uid' => $catDoc->uid, 'active_doc_id' => $catDoc->latestDoc->id ?? null]);
        $catDoc->latestDoc?->update(['work_id' => $work->id]);

        $this->notifyGeneralSuccess();

        return app_redirect_back();
    }

    /**
     * Unlink the given work from the doc.
     *
     * @param \App\Models\Corpus\Work $work
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unlinkFromDoc(Work $work): RedirectResponse
    {
        $work->update(['uid' => null, 'active_doc_id' => null]);

        $this->notifyGeneralSuccess();

        return app_redirect_back();
    }
}
