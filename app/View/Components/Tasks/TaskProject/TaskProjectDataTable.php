<?php

namespace App\View\Components\Tasks\TaskProject;

use App\Models\Tasks\TaskProject;
use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\View\ComponentAttributeBag;

class TaskProjectDataTable extends DataTable
{
    /**
     * @param Request                    $request
     * @param Builder|Relation|null|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        $query = $query ?? (new TaskProject())->newQuery();

        // @phpstan-ignore-next-line
        return $query->filter($request->all());
    }

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        /** @var string */
        $showTasks = __('tasks.task_project.show_tasks');

        return [
            'showTasks' => [
                'render' => fn ($row) => view('partials.ui.link', [
                    'href' => (request()->routeIs('my.projects.index') ? route('my.actions.tasks.index', ['view' => 'list', 'project' => $row['id']])
                        : (request()->routeIs('my.tasks.task-projects.index') ? route('my.tasks.tasks.index', ['project' => $row['id']]) : null)),
                    //                    'href' => route('my.tasks.tasks.index', ['project' => $row['id']]),
                    'linkText' => $showTasks,
                ]),
                'heading' => '',
            ],
            'title' => [
                'render' => fn ($row) => $row['title'],
                'heading' => __('interface.title'),
            ],
            'description' => [
                'render' => fn ($row) => $row['description'],
                'heading' => __('interface.description'),
            ],
            'author' => [
                'render' => fn ($row) => $row['author']->full_name,
                'heading' => __('tasks.created_by'),
            ],
            'created_at' => [
                'render' => fn ($row) => $row['created_at'] ? view('components.ui.timestamp', [
                    'timestamp' => $row['created_at']->format('Y-m-d'),
                    'attributes' => new ComponentAttributeBag(),
                ]) : '',
                'heading' => __('timestamps.created_at'),
            ],
            'actions' => [
                'render' => fn ($row) => view('partials.tasks.task-project.action-buttons', ['project' => $row]),
                'heading' => '',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return [
            'archived' => [
                'label' => __('tasks.archived'),
                'render' => fn () => view('partials.tasks.task.archived-filter', ['value' => $this->getFilterValue('archived')]),
                'value' => fn ($value) => $value,
            ],
        ];
    }
}
