<?php

namespace App\Http\Controllers\Workflows\Collaborate;

use App\Enums\Workflows\TaskStatus;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Workflows\ProjectRequest;
use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\ProductionPod;
use App\Models\Customer\Organisation;
use App\Models\Ontology\LegalDomain;
use App\Models\Workflows\Project;
use App\Models\Workflows\TaskType;
use App\Traits\Geonames\UsesJurisdictionFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\ComponentAttributeBag;

class ProjectController extends CollaborateController
{
    use UsesJurisdictionFilter;

    /** @var bool */
    protected bool $withCreate = true;

    /** @var bool */
    protected bool $withShow = true;

    /** @var bool */
    protected bool $withDelete = true;

    /** @var bool */
    protected bool $withUpdate = true;

    /** @var string */
    protected string $sortBy = 'title';

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Project::class;
    }

    /**
     * Get base resource route which will be added to the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.projects';
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
        // Specify the form request class if you have one for validating LibrarianProject inputs
        return ProjectRequest::class;
    }

    /**
     * Get the model columns that should be displayed in the index table.
     *
     * @return array<string, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'title' => fn ($row) => static::renderPartial($row, 'title-column'),
            'start_date' => fn ($row) => $row->start_date?->format('Y-m-d'),
            'due_date' => fn ($row) => $row->due_date?->format('Y-m-d'),
            'tasks_pending_count' => fn ($row) => $row->tasks_pending_count,
            'tasks_todo_count' => fn ($row) => $row->tasks_todo_count,
            'tasks_in_progress_count' => fn ($row) => $row->tasks_in_progress_count,
            'tasks_in_review_count' => fn ($row) => $row->tasks_in_review_count,
            'tasks_done_count' => fn ($row) => $row->tasks_done_count,
            'tasks_archive_count' => fn ($row) => $row->tasks_archive_count,
        ];
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
        return parent::baseQuery($request)
            ->with(['organisation', 'owner', 'location', 'sources', 'tasks', 'legalDomains', 'group'])
            ->withCount(['tasks as tasks_pending_count' => function ($query) {
                $query->where('task_status', TaskStatus::pending()->value);
            }])
            ->withCount(['tasks as tasks_todo_count' => function ($query) {
                $query->where('task_status', TaskStatus::todo()->value);
            }])
            ->withCount(['tasks as tasks_in_progress_count' => function ($query) {
                $query->where('task_status', TaskStatus::inProgress()->value);
            }])
            ->withCount(['tasks as tasks_in_review_count' => function ($query) {
                $query->where('task_status', TaskStatus::inReview()->value);
            }])
            ->withCount(['tasks as tasks_done_count' => function ($query) {
                $query->where('task_status', TaskStatus::done()->value);
            }])
            ->withCount(['tasks as tasks_archive_count' => function ($query) {
                $query->where('task_status', TaskStatus::archive()->value);
            }]);
    }

    /**
     * Get the task filters for data table.
     *
     * @return array<string, array<string, mixed>>
     */
    public function resourceFilters(): array
    {
        return [
            'jurisdiction' => $this->getJurisdictionFilter(),
            'domains' => [
                'label' => __('ontology.legal_domain.index_title'),
                'render' => fn () => view('components.ontology.legal-domain.selector', [
                    'value' => '',
                    'name' => 'domains',
                    'multiple' => true,
                    'label' => '',
                    'attributes' => new ComponentAttributeBag([
                        'annotations' => true,
                    ]),
                ]),
                'value' => fn ($value) => $value === 'all'
                    // @codeCoverageIgnoreStart
                    ? __('ontology.legal_domain.all')
                    // @codeCoverageIgnoreEnd
                    : (LegalDomain::cached($value)?->title ?? ''),
            ],
            'owner' => [
                'label' => __('workflows.project.owner'),
                'render' => fn () => view('components.workflows.tasks.task-assignment-selector', [
                    'label' => '',
                    'name' => 'assignee',
                    'attributes' => new ComponentAttributeBag([]),
                ]),
                'value' => fn ($value) => $value === 'none' ? __('tasks.unassigned') : (Collaborator::active()->find($value)?->full_name ?? ''),
            ],
            'organisation' => [
                'label' => __('customer.organisation.organisation'),
                'render' => fn () => view('components.customer.organisation.organisation-selector', [
                    'label' => '',
                    'name' => 'organisation',
                    'attributes' => new ComponentAttributeBag([]),
                ]),
                'value' => fn ($value) => $value === 'none' ? __('tasks.unassigned') : (Organisation::find($value)?->title ?? ''),
            ],
            'pod' => [
                'label' => __('workflows.project.production_pod'),
                'render' => fn () => view('components.collaborators.collaborate.production-pod-selector', [
                    'label' => '',
                    'name' => 'pod',
                    'pods' => ['' => '-'] + ProductionPod::orderBy('title')->pluck('title', 'id')->toArray(),
                    'attributes' => new ComponentAttributeBag([]),
                ]),
                'value' => fn ($value) => ProductionPod::find($value)?->title ?? '',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function showBaseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)
            ->with(['organisation', 'owner', 'location', 'sources', 'board', 'location.country', 'trackingSpecialists']);
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'forResourceTitle' => __('Projects'), // Customize this title
            'forResourceRoute' => route('collaborate.projects.index'),
            'title' => 'Project Overview', // Customize the title
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function showViewData(Request $request): array
    {
        $project = Project::with(['organisation', 'productionPod', 'location', 'sources', 'board', 'trackingSpecialists'])->findOrFail($request->route('project'));

        return [
            'project' => $project,
            'showTitle' => $project->title,
            'formWidth' => 'w-full',
            'noCard' => true,
            'taskTypes' => TaskType::whereRelation('tasks', fn ($q) => $q->where('project_id', $project->id))
                ->withCount([
                    'tasks as pending_count' => fn ($q) => $q->where('project_id', $project->id)->where('task_status', TaskStatus::pending()->value),
                    'tasks as todo_count' => fn ($q) => $q->where('project_id', $project->id)->where('task_status', TaskStatus::todo()->value),
                    'tasks as in_progress_count' => fn ($q) => $q->where('project_id', $project->id)->where('task_status', TaskStatus::inProgress()->value),
                    'tasks as in_review_count' => fn ($q) => $q->where('project_id', $project->id)->where('task_status', TaskStatus::inReview()->value),
                    'tasks as done_count' => fn ($q) => $q->where('project_id', $project->id)->where('task_status', TaskStatus::done()->value),
                ])->get(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function postUpdate(Model $model, Request $request): void
    {
        $this->postStore($model, $request);
    }

    /**
     * {@inheritDoc}
     */
    protected function postStore(Model $model, Request $request): void
    {
        $model->legalDomains()->sync($request->input('domains'));
        $model->sources()->sync($request->input('sources'));
        $model->trackingSpecialists()->sync($request->input('tracking_specialists'));
    }
}
