<?php

namespace App\Http\Controllers\Notify\Collaborate;

use App\Enums\Notify\LegalUpdatePublishedStatus;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Notify\LegalUpdateRequest;
use App\Models\Notify\LegalUpdate;
use App\Models\Ontology\LegalDomain;
use App\Models\Workflows\Task;
use App\Stores\Corpus\ContentResourceStore;
use App\Traits\Arachno\UsesSourceFilter;
use App\Traits\Geonames\UsesJurisdictionFilter;
use App\View\Components\Notify\LegalUpdate\LegalUpdateDataTable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\ComponentAttributeBag;
use Symfony\Component\HttpFoundation\Response;

class LegalUpdateController extends CollaborateController
{
    use UsesJurisdictionFilter;
    use UsesSourceFilter;

    /** @var string */
    protected string $sortBy = 'id';

    /** @var string */
    protected string $sortDirection = 'desc';

    /**
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        /** @var Builder */
        return parent::baseQuery($request)
            ->with(['legalDomains', 'primaryLocation'])
            ->when($request->routeIs('collaborate.legal-updates.show') || $request->routeIs('collaborate.legal-updates.edit'), function ($builder) {
                $builder->with([
                    'contentResource', 'notifiable', 'notifyReference.work',
                    'createdFromDoc.firstContentResource.textContentResource',
                    'maintainedWorks',
                ]);
            });
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
        return LegalUpdate::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.legal-updates';
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
        return LegalUpdateRequest::class;
    }

    /**
     * @return array<string|int, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'id' => fn ($row) => static::renderPartial($row, 'id-column'),
            'title' => fn ($row) => static::renderPartial($row, 'title-column'),
            'published' => fn ($row) => static::renderPartial($row, 'published-column'),
            'release_date' => fn ($row) => view('partials.ui.data-table.date-column', ['date' => $row->release_at])->render(),
            'date_created' => fn ($row) => view('partials.ui.data-table.date-column', ['date' => $row->created_at])->render(),
        ];
    }

    /**
     * Get the resource filters.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function resourceFilters(): array
    {
        $filters = app(LegalUpdateDataTable::class)->filters();
        unset($filters['status'], $filters['domains'], $filters['jurisdictionTypes']);

        return array_merge([
            'jurisdiction' => $this->getJurisdictionFilter(),
            'source' => $this->getSourceFilter(),
            'domains' => [
                'label' => __('ontology.legal_domain.categories'),
                'render' => fn () => view('partials.ontology.render-legal-domain-selector', [
                    'value' => null,
                    'attributes' => new ComponentAttributeBag(),
                    'name' => 'domains[]',
                    'label' => '',
                    'multiple' => true,
                ]),
                'value' => fn ($value) => LegalDomain::find($value)?->title ?? '',
                'multiple' => true,
            ],
            'published' => [
                'label' => __('notify.legal_update.published'),
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
        ], $filters);
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-7xl',
            'enctype' => 'multipart/form-data',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function editViewData(Request $request): array
    {
        /** @var LegalUpdate $update */
        $update = LegalUpdate::with(['legalDomains:id'])->find($request->route('legal_update'));
        /** @var Task|null $task */
        $task = $request->route('task') ? Task::find($request->route('task')) : null;

        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        $siblings = Task::siblings($user, $update, $task);

        return [
            'formWidth' => 'max-w-7xl',
            'enctype' => 'multipart/form-data',
            'actions' => 'partials.notify.collaborate.legal-update.header',
            'chainTasks' => $siblings,
            'legalUpdate' => $update,
            'task' => $task,
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function showViewData(Request $request): array
    {
        /** @var LegalUpdate $update */
        $update = LegalUpdate::with(['legalDomains:id'])->find($request->route('legal_update'));
        /** @var Task|null $task */
        $task = $request->get('task') ? Task::find($request->get('task')) : null;

        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        $siblings = Task::siblings($user, $update, $task);

        return [
            'formWidth' => 'max-w-7xl',
            'actions' => 'partials.notify.collaborate.legal-update.header',
            'chainTasks' => $siblings,
            'legalUpdate' => $update,
            'task' => $task,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function shouldAuthorise(): bool
    {
        /** @var Request $request */
        $request = request();

        return !$request->routeIs('collaborate.legal-updates.edit')
            && !$request->routeIs('collaborate.legal-updates.update')
            && !$request->routeIs('collaborate.legal-updates.create')
            && !$request->routeIs('collaborate.legal-updates.store');
    }

    /**
     * {@inheritDoc}
     */
    public function edit(Request $request, int $resourceId): View
    {
        $update = $this->baseQuery($request)->findOrFail($resourceId);
        $task = $request->route('task') ? Task::findOrFail($request->route('task')) : null;
        $this->authorize('edit', [$update, $task]);

        return parent::edit($request, $resourceId);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $resourceId): RedirectResponse
    {
        /** @var Request $request */
        $request = request();
        $update = $this->baseQuery($request)->findOrFail($resourceId);
        $task = $request->route('task') ? Task::findOrFail($request->route('task')) : null;
        $this->authorize('edit', [$update, $task]);

        return parent::update($resourceId);
    }

    /**
     * {@inheritDoc}
     */
    protected function redirectAfterUpdate(Model $model): string
    {
        /** @var Request $request */
        $request = request();
        /** @var Task|null $task */
        $task = $request->route('task') ? Task::findOrFail($request->route('task')) : null;

        $close = $request->has('and_close');

        // @codeCoverageIgnoreStart
        /** @var LegalUpdate $model */
        return $close
            ? route('collaborate.legal-updates.show', ['legal_update' => $model->id, 'task' => $task->id ?? null])
            : route('collaborate.legal-updates.edit', ['legal_update' => $model->id, 'task' => $task->id ?? null]);
        // @codeCoverageIgnoreEnd
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
    public function store(): RedirectResponse
    {
        /** @var Request $request */
        $request = request();

        $task = $request->route('task') ? Task::findOrFail($request->route('task')) : null;

        $this->authorize('create', [new LegalUpdate(), $task]);

        return parent::store();
    }

    /**
     * {@inheritDoc}
     */
    public function create(Request $request): View|Response
    {
        $task = $request->route('task') ? Task::findOrFail($request->route('task')) : null;

        $this->authorize('create', [new LegalUpdate(), $task]);

        return parent::create($request);
    }

    /**
     * {@inheritDoc}
     */
    protected function postStore(Model $model, Request $request): void
    {
        $this->updateResource($model, $request);
    }

    /**
     * {@inheritDoc}
     */
    protected function postUpdate(Model $model, Request $request): void
    {
        $this->updateResource($model, $request);
    }

    /**
     * Store the resource and update the legal update.
     *
     * @param Model   $model
     * @param Request $request
     *
     * @return void
     */
    protected function updateResource(Model $model, Request $request): void
    {
        if ($request->hasFile('content_resource_file')) {
            /** @var UploadedFile $file */
            $file = $request->file('content_resource_file');
            $resource = app(ContentResourceStore::class)->storeResource($file->getContent(), $file->getClientMimeType());
            /** @var LegalUpdate $model */
            $model->update(['content_resource_id' => $resource->id]);
        }

        $domains = $request->get('domains', []);

        $model->legalDomains()->sync($domains);
    }

    /**
     * Publish the legal update.
     *
     * @param LegalUpdate $legalUpdate
     *
     * @return RedirectResponse
     */
    public function publish(LegalUpdate $legalUpdate): RedirectResponse
    {
        if (!$legalUpdate->release_at || strlen($legalUpdate->highlights ?? '') < 10 || strlen($legalUpdate->update_report ?? '') < 10) {
            /** @var string $message */
            $message = __('notify.legal_update.failed_to_publish');
            $this->notifyErrorMessage($message);

            return redirect()->back();
        }

        $legalUpdate->update(['status' => LegalUpdatePublishedStatus::PUBLISHED->value]);

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.legal-updates.show', ['legal_update' => $legalUpdate->id]);
    }

    /**
     * Publish the legal update.
     *
     * @param LegalUpdate $legalUpdate
     *
     * @return RedirectResponse
     */
    public function notApplicable(LegalUpdate $legalUpdate): RedirectResponse
    {
        if ($legalUpdate->status !== LegalUpdatePublishedStatus::PUBLISHED->value) {
            /** @var string $message */
            $message = __('notify.legal_update.failed_to_mark_not_applicable');
            $this->notifyErrorMessage($message);

            return redirect()->back();
        }

        $legalUpdate->update(['status' => LegalUpdatePublishedStatus::NOT_APPLICABLE->value]);

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.legal-updates.show', ['legal_update' => $legalUpdate->id]);
    }
}
