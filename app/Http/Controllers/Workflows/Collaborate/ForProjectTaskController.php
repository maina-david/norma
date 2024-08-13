<?php

namespace App\Http\Controllers\Workflows\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Services\Workflows\Tasks\UserTaskFilters;
use App\Models\Workflows\Project;
use App\Models\Workflows\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\ComponentAttributeBag;

class ForProjectTaskController extends CollaborateController
{
    protected bool $searchable = true;
    protected bool $withShow = true;
    protected bool $withCreate = false;
    protected bool $withUpdate = false;
    protected bool $withDelete = false;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Task::class;
    }

    protected static function forResource(): ?string
    {
        return Project::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.projects.tasks';
    }

    /**
     * @codeCoverageIgnore
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return '';
    }

    /**
     * Get the model columns that should be displayed in the index table. Use the dot notation for relations.
     * e.g. ['profile.id', 'name'].
     *
     * @return array<string|int, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'title' => fn ($row) => static::renderPartial($row, 'title-column'),
            'assigned_to' => fn ($row) => $row->assignee?->full_name ?? '',
            'status' => fn ($row) => view('components.workflows.tasks.task-status-badge', [
                'status' => $row->task_status,
                'attributes' => new ComponentAttributeBag(),
            ]),
        ];
    }

    public function viewPath(): string
    {
        return 'pages.workflows.collaborate.task.for-project';
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'project' => $this->getForResource(),
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
            'project' => $request->route('project'),
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
        /** @var Project */
        $project = $this->getForResource();

        return $project->tasks()->getQuery()->with(['assignee', 'taskType']);
    }

    /**
     * Get the resource filters.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function resourceFilters(): array
    {
        return app(UserTaskFilters::class)->resourceFilters();
    }
}
