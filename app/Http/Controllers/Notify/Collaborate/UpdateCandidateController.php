<?php

namespace App\Http\Controllers\Notify\Collaborate;

use App\Enums\Corpus\UpdateCandidateActionStatus;
use App\Enums\Notify\NotificationStatus;
use App\Enums\Workflows\DocumentType;
use App\Enums\Workflows\YesNoPending;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Notify\UpdateCandidateRequest;
use App\Models\Arachno\Source;
use App\Models\Auth\User;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Models\Notify\NotificationAction;
use App\Models\Notify\NotificationHandover;
use App\Models\Notify\UpdateCandidate;
use App\Models\Ontology\LegalDomain;
use App\Models\Workflows\Document;
use App\Models\Workflows\Task;
use App\Stores\Corpus\ContentResourceStore;
use App\Traits\Geonames\UsesJurisdictionFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\ComponentAttributeBag;
use ReflectionException;

class UpdateCandidateController extends CollaborateController
{
    use UsesJurisdictionFilter;

    /** @var string */
    protected string $sortDirection = 'desc';

    /**
     * Create a generic task if the user has permissions.
     *
     * @return Task
     */
    protected function genericTask(): Task
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->can('collaborate.corpus.doc.update-candidate.manage-without-task'), 404);

        $task = new Task();
        $task->id = 0;

        $task->setRelation('document', new Document([]));

        return $task;
    }

    /**
     * Get the associated task from the request.
     *
     * @param Request $request
     *
     * @return Task
     */
    protected function getTask(Request $request): Task
    {
        /** @var string $task */
        $task = $request->route('task');

        /** @var Task $fetched */
        $fetched = $task == '0' ? $this->genericTask() : Task::with(['document'])
            ->whereRelation('document', 'document_type', DocumentType::SOURCE_MONITORING->value)
            ->whereKey($task)
            ->firstOrFail();

        return $fetched;
    }

    /**
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        $task = $this->getTask($request);
        $source = $task->document->source_id ?? null;

        /** @var Builder */
        return parent::baseQuery($request)
            ->where('for_update', true)
            ->when($source, fn ($builder) => $builder->where('source_id', $source))
//            ->when($task->document && $task->document->locations->isNotEmpty(), function ($builder) use ($task) {
//                /** @var Document $document */
//                $document = $task->document;
//                $builder->whereIn('primary_location_id', $document->locations->pluck('id')->toArray());
//            })
//            ->when($task->document->legalDomains->isNotEmpty(), function ($builder) use ($task) {
//                $builder->whereHas('legalDomains', function ($query) use ($task) {
//                    $query->whereKey($task->document->locations->pluck('id')->toArray());
//                });
//            })
            ->with([
                'updateCandidate', 'docMeta', 'primaryLocation', 'legalDomains', 'categories', 'keywords',
                'updateCandidate.notificationStatusReason', 'latestLegalUpdate', 'updateCandidate.notificationActions',
                'firstContentResource.textContentResource', 'updateCandidate.legislation.handovers',
            ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function authoriseAction(string $action, mixed $arguments = []): void
    {
        $permission = match ($action) {
            'create' => 'create',
            'edit', 'update' => 'update',
            'delete' => 'delete',
            // @codeCoverageIgnoreStart
            'restore' => 'restore',
            // @codeCoverageIgnoreEnd
            default => 'viewAny',
        };

        /** @var Request $request */
        $request = request();
        $this->authorize('view', $this->getTask($request));
        $this->authorize("collaborate.corpus.doc.update-candidate.{$permission}");

        /** @var User $user */
        $user = $request->user();

        if ($permission === 'update') {
            /** @var Doc $resource */
            $resource = $this->baseQuery($request)->findOrFail($request->route('doc'));
            abort_unless($this->canEditDoc($resource) || $user->can('collaborate.corpus.doc.update-candidate.override-edit'), 404);
        }
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
        return Doc::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.docs-for-update.tasks';
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
        return UpdateCandidateRequest::class;
    }

    /**
     * @return array<string|int, mixed>
     */
    protected static function indexColumns(): array
    {
        $fields = [
            'title' => fn ($row) => static::renderPartial($row, 'candidate-title-column'),
            'preview' => fn ($row) => static::renderPartial($row, 'preview-column', ['withText' => true]),
        ];

        $permissioned = [
            'justification' => fn ($row) => static::renderPartial($row, 'justification-column'),
            'check_sources_review' => fn ($row) => static::renderPartial($row, 'checked-column'),
            'notification_status' => fn ($row) => static::renderPartial($row, 'notification-status-column'),
            'notification_task' => fn ($row) => static::renderPartial($row, 'notification-task-column'),
            'additional_actions' => fn ($row) => static::renderPartial($row, 'additional-actions-column'),
            'affected_legislation' => fn ($row) => static::renderPartial($row, 'affected-column'),
            //            'handover_update' => fn ($row) => static::renderPartial($row, 'handover-update-column'),
        ];

        $permissionMap = [
            'justification' => 'collaborate.corpus.doc.update-candidate.view-justification',
            'check_sources_review' => 'collaborate.corpus.doc.update-candidate.view-checked-by',
            'notification_status' => 'collaborate.corpus.doc.update-candidate.view-notification-status',
            'notification_task' => 'collaborate.corpus.doc.update-candidate.view-notification-task',
            'additional_actions' => 'collaborate.corpus.doc.update-candidate.view-additional-actions',
            'affected_legislation' => 'collaborate.corpus.doc.update-candidate.view-affected-legislation',
            'handover_update' => 'collaborate.corpus.doc.update-candidate.view-handover-updates',
        ];

        /** @var User $user */
        $user = Auth::user();

        foreach ($permissioned as $key => $value) {
            if ($user->can($permissionMap[$key])) {
                $fields[$key] = $value;
            }
        }

        return $fields;
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        $task = $this->getTask($request);

        return [
            'fluid' => true,
            'preBreadcrumbView' => 'pages.corpus.collaborate.doc.partials.doc-task-details',
            'preCreateButtonView' => 'pages.corpus.collaborate.doc.partials.candidate-task',
            'title' => __('corpus.doc.manage_updates'),
            'task' => $task,
            'tableClass' => 'manage-updates-table',
            'pageHead' => [
                'resources/js/vue/sharable-components.js',
            ],
        ];
    }

    /**
     * If the doc can be edited.
     *
     * @param Doc $doc
     *
     * @return bool
     */
    protected function canEditDoc(Doc $doc): bool
    {
        /** @var User $user */
        $user = Auth::user();
        $isChecked = ((bool) ($doc->updateCandidate->checked_at ?? null)) && ((bool) ($doc->updateCandidate->checked_by_id ?? null));
        $isAuthor = ($doc->updateCandidate->author_id ?? null) === $user->id;
        $isManual = $doc->updateCandidate->manual ?? false;

        return $isManual && $isAuthor && !$isChecked;
    }

    /**
     * Render the timestamp component.
     *
     * @param Doc $row
     *
     * @throws ReflectionException
     *
     * @return string
     */
    protected function renderActionButtons(Model $row): string
    {
        /** @var User $user */
        $user = Auth::user();

        $override = $this->canEditDoc($row);

        return view('components.ui.table-action-buttons', [
            'attributes' => new ComponentAttributeBag(),
            'withUpdate' => $override || $user->can('collaborate.corpus.doc.update-candidate.override-edit'),
            'withDelete' => $override || $user->can('collaborate.corpus.doc.update-candidate.override-delete'),
            'basePermission' => $this->permissionPrefix(),
            'baseRoute' => static::resourceRoute(),
            'baseRouteParams' => $this->resourceRouteParams(),
            'resourceId' => $row->id,
        ])->render();
    }

    /**
     * Get the applicable jurisdictions.
     *
     * @param Request $request
     *
     * @return array<int, string>
     */
    protected function getLocations(Request $request): array
    {
        //        $task = $this->getTask($request);
        //
        //        return Location::whereRelation('sources', 'id', $task->document->source_id ?? null)
        //            ->pluck('title', 'id')
        //            ->toArray();
        //
        return [];
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-3xl',
            'enctype' => 'multipart/form-data',
            'locations' => $this->getLocations($request),
            'title' => __('corpus.doc.manage_updates'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function editViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-3xl',
            'enctype' => 'multipart/form-data',
            'locations' => $this->getLocations($request),
            'title' => __('corpus.doc.manage_updates'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function showViewData(Request $request): array
    {
        /** @var Doc $resource */
        $resource = $this->baseQuery($request)->findOrFail($request->route('doc'));

        /** @var User $user */
        $user = $request->user();

        return [
            'formWidth' => 'max-w-7xl',
            'title' => __('corpus.doc.manage_updates'),
            'notEditable' => !($this->canEditDoc($resource) || $user->can('collaborate.corpus.doc.update-candidate.override-edit')),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceRouteParams(): array
    {
        /** @var Request $request */
        $request = request();

        return [
            'task' => $request->route('task'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function redirectAfterStore(Model $model): string
    {
        /** @var Request $request */
        $request = request();

        return route('collaborate.docs-for-update.tasks.index', ['task' => $request->route('task')]);
    }

    /**
     * {@inheritDoc}
     */
    protected function redirectAfterUpdate(Model $model): string
    {
        return $this->redirectAfterStore($model);
    }

    /**
     * Pre-Store hook.
     *
     * @param Doc                    $model
     * @param UpdateCandidateRequest $request
     *
     * @return void
     */
    protected function preStore(Model $model, Request $request): void
    {
        $task = $this->getTask($request);

        $model->fill([
            'primary_location_id' => $request->get('primary_location_id'),
            'source_id' => $request->get('source_id', $task->document->source_id ?? null),
            'title' => $request->get('title'),
            'first_content_resource_id' => $this->updateResource($request)?->id,
            'for_update' => true,
        ]);
    }

    /**
     * Pre-Update hook.
     *
     * @param Doc     $model
     * @param Request $request
     *
     * @return void
     */
    protected function preUpdate(Model $model, Request $request): void
    {
        $task = $this->getTask($request);

        $model->fill([
            'title' => $request->get('title'),
            'source_id' => $request->get('source_id', $task->document->source_id ?? null),
            'first_content_resource_id' => $this->updateResource($request)?->id ?? $model->first_content_resource_id,
        ]);
    }

    /**
     * Update the legal domains and the document meta.
     *
     * @param Doc                    $model
     * @param UpdateCandidateRequest $request
     *
     * @return void
     */
    protected function updateDomainsAndMeta(Model $model, Request $request): void
    {
        /** @var array<int, string> $domains */
        $domains = $request->get('domains', []);

        $model->legalDomains()->sync($domains);

        $model->load(['docMeta']);

        $model->docMeta->update([
            'title_translation' => $request->get('title_translation'),
            'language_code' => $request->get('language_code'),
            'source_url' => $request->get('source_url'),
            'work_date' => $request->get('document_date'),
        ]);
    }

    /**
     * Post-Store hook.
     *
     * @param Doc                    $model
     * @param UpdateCandidateRequest $request
     *
     * @return void
     */
    protected function postStore(Model $model, Request $request): void
    {
        $this->updateDomainsAndMeta($model, $request);

        UpdateCandidate::create([
            'doc_id' => $model->id,
            'manual' => true,
            'justification_status' => false,
            'justification' => '',
            'notification_status' => NotificationStatus::UNKNOWN->value,
            'checked_comment' => '',
            'affected_legislation' => [],
            'affected_available' => false,
            'author_id' => Auth::id(),
        ]);
    }

    /**
     * Post-Update hook.
     *
     * @param Doc                    $model
     * @param UpdateCandidateRequest $request
     *
     * @return void
     */
    protected function postUpdate(Model $model, Request $request): void
    {
        $this->updateDomainsAndMeta($model, $request);
    }

    /**
     * Store the resource.
     *
     * @param Request $request
     *
     * @return ContentResource|null
     */
    protected function updateResource(Request $request): ?ContentResource
    {
        if ($request->hasFile('content_resource_file')) {
            /** @var UploadedFile $file */
            $file = $request->file('content_resource_file');

            return app(ContentResourceStore::class)->storeResource($file->getContent(), $file->getClientMimeType());
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    protected function getResourceId(): ?int
    {
        /** @var int|null $doc */
        $doc = request()->route('doc');

        return $doc ? (int) $doc : null;
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceFilters(): array
    {
        $filters = [
            'jurisdiction' => $this->getJurisdictionFilter(),
            'justification' => [
                'label' => __('corpus.doc.justification'),
                'render' => function () {
                    return view('partials.ui.collaborate.input-filter', [
                        'attributes' => new ComponentAttributeBag([
                            'type' => 'select',
                            'options' => YesNoPending::forSelector(true),
                            'label' => '',
                            'value' => '',
                            'name' => 'justification',
                            '@change' => '$dispatch(\'changed\', $el.value)',
                        ]),
                    ]);
                },
                'value' => fn ($value) => YesNoPending::lang()[$value] ?? '',
            ],
            'check-sources' => [
                'label' => __('corpus.doc.check_sources_review'),
                'render' => function () {
                    return view('partials.ui.collaborate.input-filter', [
                        'attributes' => new ComponentAttributeBag([
                            'type' => 'select',
                            'options' => YesNoPending::forSelector(true),
                            'label' => '',
                            'value' => '',
                            'name' => 'check-sources',
                            '@change' => '$dispatch(\'changed\', $el.value)',
                        ]),
                    ]);
                },
                'value' => fn ($value) => YesNoPending::lang()[$value] ?? '',
            ],
            'notification-status' => [
                'label' => __('corpus.doc.notification_status'),
                'multiple' => true,
                'render' => function () {
                    return view('partials.ui.collaborate.input-filter', [
                        'attributes' => new ComponentAttributeBag([
                            'type' => 'select',
                            'options' => NotificationStatus::forSelector(true),
                            'label' => '',
                            'value' => '',
                            'name' => 'notification-status[]',
                            '@change' => '$dispatch(\'changed\', Array.from($el.selectedOptions).map(function (e) { return e.value }))',
                            'multiple' => true,
                        ]),
                    ]);
                },
                'value' => fn ($value) => NotificationStatus::lang()[$value] ?? '',
            ],
            'start-date' => [
                'label' => __('workflows.task.work_start_date'),
                'render' => function () {
                    return view('partials.ui.collaborate.input-filter', [
                        'attributes' => new ComponentAttributeBag([
                            'type' => 'date',
                            'label' => '',
                            'value' => '',
                            'name' => 'start-date',
                            '@change' => '$dispatch(\'changed\', $el.value)',
                        ]),
                    ]);
                },
                'value' => fn ($value) => $value,
            ],
            'end-date' => [
                'label' => __('workflows.task.work_end_date'),
                'render' => function () {
                    return view('partials.ui.collaborate.input-filter', [
                        'attributes' => new ComponentAttributeBag([
                            'type' => 'date',
                            'label' => '',
                            'value' => '',
                            'name' => 'end-date',
                            '@change' => '$dispatch(\'changed\', $el.value)',
                        ]),
                    ]);
                },
                'value' => fn ($value) => $value,
            ],
            'additional-actions' => [
                'label' => __('corpus.doc.additional_actions'),
                'render' => function () {
                    $options = NotificationAction::pluck('text', 'id');
                    $options->put('', '');

                    return view('partials.ui.collaborate.input-filter', [
                        'attributes' => new ComponentAttributeBag([
                            'type' => 'select',
                            'options' => $options->toArray(),
                            'label' => '',
                            'value' => '',
                            'name' => 'additional-actions',
                            '@change' => '$dispatch(\'changed\', $el.value)',
                        ]),
                    ]);
                },
                'value' => fn ($value) => NotificationAction::pluck('text', 'id')->get($value) ?? '',
            ],
            'handover-update' => [
                'label' => __('corpus.doc.handover_update'),
                'multiple' => true,
                'render' => function () {
                    $options = NotificationHandover::pluck('text', 'id');
                    $options->put('', '');

                    return view('partials.ui.collaborate.input-filter', [
                        'attributes' => new ComponentAttributeBag([
                            'type' => 'select',
                            'options' => $options->toArray(),
                            'label' => '',
                            'value' => '',
                            'name' => 'handover-update[]',
                            '@change' => '$dispatch(\'changed\', Array.from($el.selectedOptions).map(function (e) { return e.value }))',
                            'multiple' => true,
                        ]),
                    ]);
                },
                'value' => fn ($value) => NotificationHandover::find($value)?->text ?? '',
            ],
            'handover-update-status' => [
                'label' => __('corpus.doc.handover_update_status'),
                'multiple' => true,
                'render' => function () {
                    return view('partials.ui.collaborate.input-filter', [
                        'attributes' => new ComponentAttributeBag([
                            'type' => 'select',
                            'options' => UpdateCandidateActionStatus::forSelector(true),
                            'label' => '',
                            'value' => '',
                            'name' => 'handover-update-status[]',
                            '@change' => '$dispatch(\'changed\', Array.from($el.selectedOptions).map(function (e) { return e.value }))',
                            'multiple' => true,
                        ]),
                    ]);
                },
                'value' => fn ($value) => UpdateCandidateActionStatus::lang()[$value] ?? '',
            ],
            'source' => [
                'label' => __('corpus.work.source'),
                'render' => function () {
                    $options = Source::pluck('title', 'id');
                    $options->put('', '');

                    return view('partials.ui.collaborate.input-filter', [
                        'attributes' => new ComponentAttributeBag([
                            'type' => 'select',
                            'options' => $options->toArray(),
                            'label' => '',
                            'value' => '',
                            'name' => 'notification-status',
                            '@change' => '$dispatch(\'changed\', $el.value)',
                        ]),
                    ]);
                },
                'value' => fn ($value) => Source::find($value)?->title ?? '',
            ],
            'domains' => [
                'label' => __('ontology.legal_domain.categories'),
                'multiple' => true,
                'render' => fn () => view('partials.ontology.render-legal-domain-selector', [
                    'value' => null,
                    'attributes' => new ComponentAttributeBag(),
                    'name' => 'domains',
                    'label' => '',
                    'multiple' => true,
                ]),
                'value' => fn ($value) => LegalDomain::find($value)?->title ?? '',
            ],
            'published' => [
                'label' => __('notify.legal_update.legal_update_published'),
                'render' => fn () => view('partials.ui.collaborate.input-filter', [
                    'attributes' => new ComponentAttributeBag([
                        'label' => '',
                        'value' => $this->getFilterValue('published'),
                        'name' => 'published',
                        'type' => 'select',
                        'options' => ['' => '', 'Yes' => __('interface.yes'), 'No' => __('interface.no')],
                    ]),
                ]),
                'value' => fn ($value) => in_array($value, ['Yes', 'No']) ? $value : '',
            ],
        ];

        return $filters;
    }
}
