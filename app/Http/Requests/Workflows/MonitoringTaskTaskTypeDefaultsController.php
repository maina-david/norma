<?php

namespace App\Http\Requests\Workflows;

use App\Enums\Application\ApplicationType;
use App\Http\Controllers\Controller;
use App\Models\Workflows\Board;
use App\Models\Workflows\MonitoringTaskConfig;
use App\Models\Workflows\TaskType;
use App\Traits\Workflows\UsesBoardConfiguration;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MonitoringTaskTaskTypeDefaultsController extends Controller
{
    use UsesBoardConfiguration;

    /**
     * Redirect to the current task config.
     *
     * @param \App\Models\Workflows\MonitoringTaskConfig $config
     *
     * @return RedirectResponse
     */
    public function index(MonitoringTaskConfig $config): RedirectResponse
    {
        return redirect()->route('collaborate.monitoring-task-configs.show', $config->id);
    }

    /**
     * Get the default values for the given task type.
     *
     * @param \App\Models\Workflows\MonitoringTaskConfig $config
     * @param int                                        $taskTypeId
     *
     * @return array<string, mixed>
     */
    protected function getDefaultsFromConfig(MonitoringTaskConfig $config, int $taskTypeId): array
    {
        /** @var Board $board */
        $board = $config->board;

        /** @var array<int, mixed> $defaults */
        $defaults = $board->task_type_defaults;

        return array_merge($defaults[$taskTypeId] ?? [], $config->tasks[$taskTypeId] ?? []);
    }

    /**
     * Get the task type defaults.
     *
     * @param \App\Models\Workflows\MonitoringTaskConfig $config
     * @param TaskType                                   $taskType
     *
     * @return View
     */
    public function show(MonitoringTaskConfig $config, TaskType $taskType): View
    {
        $config->load(['board', 'group']);
        /** @var Board $board */
        $board = $config->board;

        $previousTypes = $this->getDependableTypes($board, $taskType);

        $defaults = (object) $this->getDefaultsFromConfig($config, $taskType->id);

        $users = $this->getUsers($defaults);

        /** @var \Illuminate\View\View */
        return view('pages.crud.edit', [
            'application' => ApplicationType::collaborate(),
            'appLayout' => 'layouts.collaborate',
            'baseRoute' => 'collaborate.monitoring-task-configs.task-types',
            'baseRouteParams' => ['task_type' => $taskType->id, 'config' => $config->id],
            'langFile' => 'workflows.board_task_type_defaults',
            'resource' => $config,
            'form' => 'partials.workflows.collaborate.task.defaults',
            'previousTypes' => $previousTypes,
            'permission' => 'collaborate.workflows.monitoring-task-config',
            'defaults' => $defaults,
            'group' => $config->group,
            'users' => $users,
            'formWidth' => 'max-w-7xl',
            'langParams' => [
                'board' => $config->title,
                'type' => $taskType->name,
            ],
            'excludeFields' => ['start_date', 'due_date', 'depends_on', 'group_id', 'set_current_units', 'auto_payment', 'title'],
        ]);
    }

    /**
     * Update the given task type defaults.
     *
     * @param TaskTypeDefaultsRequest                    $request
     * @param \App\Models\Workflows\MonitoringTaskConfig $config
     * @param TaskType                                   $taskType
     *
     * @return RedirectResponse
     */
    public function update(TaskTypeDefaultsRequest $request, MonitoringTaskConfig $config, TaskType $taskType): RedirectResponse
    {
        $toUnset = [
            'group_id',
            'due_date',
            'title',
            'task_status',
            'auto_payment',
            'task_type_id',
            'parent_task_id',
            'requires_rating',
            'set_current_units',
            'depends_on_task_id',
            'move_dependent_task',
            'create_one_for_every',
            'publish_related_work',
            'generate_reference_extracts',
            'set_current_due_date',
            'set_current_units_type',
            'set_dependent_due_date',
            'set_dependent_complexity',
            'set_dependent_units_type',
            'set_current_task_duration',
            'set_current_units_trigger',
            'move_dependent_task_status',
            'set_current_units_multiple',
            'move_dependent_task_trigger',
            'set_dependent_task_duration',
            'publish_related_work_trigger',
            'generate_reference_extracts_trigger',
            'set_current_due_date_trigger',
            'set_dependent_units_multiple',
            'set_dependent_due_date_trigger',
            'validations',
        ];

        $config->load(['board', 'group']);

        $defaults = $config->tasks ?? [];

        $defaults[$taskType->id] = array_merge(
            $this->getDefaultsFromConfig($config, $taskType->id),
            $request->validated()
        );

        foreach ($defaults as $taskTypeId => $defs) {
            $defaults[$taskTypeId] = $this->stringsToNumerics($defs);

            foreach ($toUnset as $remove) {
                unset($defaults[$taskTypeId][$remove]);
            }
        }

        $config->update(['tasks' => $defaults]);

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.monitoring-task-configs.show', ['monitoring_task_config' => $config->id]);
    }
}
