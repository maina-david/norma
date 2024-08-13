<?php

namespace App\View\Components\Tasks\Task;

use App\Enums\Tasks\TaskPriority;
use App\Enums\Tasks\TaskStatus;
use App\Enums\Tasks\TaskType;
use App\Http\Controllers\Traits\DecodesHashids;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskProject;
use App\Services\Customer\ActiveLibryosManager;
use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\View\ComponentAttributeBag;

class TaskDataTable extends DataTable
{
    use DecodesHashids;

    /**
     * @param Request                    $request
     * @param Builder|Relation|null|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        $query = $query ?? (new Task())->newQuery();

        /** @var array<mixed> */
        $filters = $request->all();
        if (isset($filters['assignee'])) {
            $filters['assignee'] = $this->decodeHashId($filters['assignee'], User::class);
        }

        /** @var array<int> */
        $statuses = $request->input('statuses', []);
        if (empty($statuses) || !in_array(TaskStatus::done()->value, $statuses)) {
            if (!$query instanceof Relation) {
                $query->excludingDone();
            }
        }

        $query->with(['libryo:id,title']);

        // @phpstan-ignore-next-line
        return $query->filter($filters)->orderBy('due_on');
    }

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        $manager = app(ActiveLibryosManager::class);

        return [
            'assignee_avatar' => [
                'render' => fn ($row) => $row['assignee'] ? view('components.ui.user-avatar', [
                    'user' => $row['assignee'],
                    'dimensions' => 10,
                ]) : '',
                'heading' => '',
            ],
            'title' => [
                'render' => fn ($row) => $row['title'],
                'heading' => __('interface.title'),
            ],
            'title_link' => [
                'render' => fn ($row) => view('components.tasks.task.task-link', [
                    'attributes' => new ComponentAttributeBag(),
                    'task' => $row,
                    'newTab' => false,
                    'singleMode' => $manager->isSingleMode(),
                ]),
                'heading' => __('interface.title'),
            ],
            'status' => [
                'render' => fn ($row) => view('partials.tasks.task.status', [
                    'task' => $row,
                ]),
                'heading' => __('tasks.status'),
            ],
            'priority' => [
                'render' => fn ($row) => TaskPriority::lang()[$row['priority']] ?? '',
                'heading' => __('tasks.priority'),
                'align' => 'center',
            ],
            'due_on' => [
                'render' => fn ($row) => $row['due_on'] ? view('components.ui.timestamp', [
                    'timestamp' => $row['due_on']->format('Y-m-d'),
                    'attributes' => new ComponentAttributeBag(),
                ]) : '',
                'heading' => __('tasks.due_on'),
                'align' => 'center',
            ],
            'completed_at' => [
                'render' => fn ($row) => view('partials.tasks.task.completed-at', [
                    'task' => $row,
                ]),
                'heading' => __('tasks.completed_on'),
                'align' => 'center',
            ],
            'project' => [
                'render' => fn ($row) => $row['project']?->title ?? '',
                'heading' => __('tasks.project'),
            ],
            'task_type' => [
                'render' => fn ($row) => view('partials.tasks.task.type', [
                    'type' => $row['taskable_type'],
                    'task' => $row,
                ]),
                'heading' => __('tasks.type'),
                'align' => 'center',
            ],
            'compact' => [
                'render' => fn ($row) => view('partials.tasks.task.compact-list-item', [
                    'task' => $row,
                    'singleMode' => $manager->isSingleMode(),
                ]),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return [
            'statuses' => [
                'label' => __('tasks.status'),
                'render' => fn () => view('components.tasks.status-select', [
                    'value' => $this->getFilterValue('statuses'),
                    'name' => 'statuses',
                    'tomselected' => false,
                    'attributes' => new ComponentAttributeBag(),
                    'multiple' => true,
                ]),
                'value' => fn ($value) => TaskStatus::lang()[$value] ?? '',
                'multiple' => true,
            ],
            'type' => [
                'label' => __('tasks.type'),
                'render' => fn () => view('components.tasks.type-select', [
                    'value' => $this->getFilterValue('type'),
                ]),
                'value' => fn ($value) => TaskType::keysLang()[$value] ?? '',
            ],
            'priority' => [
                'label' => __('tasks.priority'),
                'render' => fn () => view('components.tasks.priority-select', [
                    'value' => $this->getFilterValue('priority'),
                    'attributes' => new ComponentAttributeBag(),
                ]),
                'value' => fn ($value) => TaskPriority::lang()[$value] ?? '',
            ],
            'assignee' => [
                'label' => __('tasks.assigned_to'),
                'render' => fn () => view('partials.tasks.task.assignee-filter', [
                    'value' => $this->getFilterValue('assignee'),
                ]),
                // @codeCoverageIgnoreStart
                'value' => function ($value) {
                    /** @var Organisation */
                    $organisation = app(ActiveLibryosManager::class)->getActiveOrganisation();

                    $value = $this->decodeHashId($value, User::class);

                    /** @var User */
                    $user = User::inOrganisation($organisation->id)->find($value);

                    return $user->full_name ?? '';
                },
                // @codeCoverageIgnoreEnd
            ],
            'archived' => [
                'label' => __('tasks.archived'),
                'render' => fn () => view('partials.tasks.task.archived-filter', ['value' => $this->getFilterValue('archived')]),
                'value' => fn ($value) => $value,
            ],
            'overdue' => [
                'label' => __('tasks.overdue'),
                'render' => fn () => view('partials.tasks.task.overdue-filter', ['value' => $this->getFilterValue('overdue')]),
                'value' => fn ($value) => $value,
            ],
            'project' => [
                'label' => __('tasks.project'),
                'render' => fn () => view('partials.tasks.task.project-filter', ['value' => (int) $this->getFilterValue('project')]),
                'value' => fn ($value) => TaskProject::find($value)?->title ?? '',
            ],
            'start_date' => [
                'label' => __('tasks.due_date_start'),
                'render' => fn () => view('partials.tasks.task.due-date-filter', [
                    'value' => $this->getFilterValue('start_date'),
                    'name' => 'start_date',
                    'placeholder' => 'Start',
                ]),
                'value' => fn ($value) => $value ?? '',
            ],
            'end_date' => [
                'label' => __('tasks.due_date_end'),
                'render' => fn () => view('partials.tasks.task.due-date-filter', [
                    'value' => $this->getFilterValue('end_date'),
                    'name' => 'end_date',
                    'placeholder' => 'End',
                ]),
                'value' => fn ($value) => $value ?? '',
            ],
        ];
    }
}
