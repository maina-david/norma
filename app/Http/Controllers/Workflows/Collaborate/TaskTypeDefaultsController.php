<?php

namespace App\Http\Controllers\Workflows\Collaborate;

use App\Enums\Application\ApplicationType;
use App\Enums\Workflows\TaskTypeOption;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workflows\TaskTypeDefaultsRequest;
use App\Models\Collaborators\Group;
use App\Models\Workflows\Board;
use App\Models\Workflows\TaskType;
use App\Traits\Workflows\UsesBoardConfiguration;
use Illuminate\Http\RedirectResponse;

class TaskTypeDefaultsController extends Controller
{
    use UsesBoardConfiguration;

    /**
     * Redirect to the current board.
     *
     * @param Board $board
     *
     * @return RedirectResponse
     */
    public function index(Board $board): RedirectResponse
    {
        return redirect()->route('collaborate.boards.show', $board->id);
    }

    /**
     * Get the task type defaults.
     *
     * @param Board    $board
     * @param TaskType $taskType
     *
     * @return mixed
     */
    public function show(Board $board, TaskType $taskType): mixed
    {
        $previousTypes = $this->getDependableTypes($board, $taskType);

        /** @var array<int, mixed> $defaults */
        $defaults = $board->task_type_defaults;
        $defaults = (object) ($defaults[$taskType->id] ?? []);

        $group = isset($defaults->group_id) ? Group::find($defaults->group_id, ['id', 'title']) : null;

        $users = $this->getUsers($defaults);

        $taskTypeOptions = TaskTypeOption::forSelector();
        $taskTypeWithoutCompleted = array_values(array_filter($taskTypeOptions, fn ($option) => TaskTypeOption::COMPLETE->value != $option['value']));

        return view('pages.crud.edit', [
            'application' => ApplicationType::collaborate(),
            'appLayout' => 'layouts.collaborate',
            'baseRoute' => 'collaborate.boards.task-types',
            'baseRouteParams' => ['task_type' => $taskType->id, 'board' => $board->id],
            'langFile' => 'workflows.board_task_type_defaults',
            'resource' => $board,
            'form' => 'partials.workflows.collaborate.task.defaults',
            'previousTypes' => $previousTypes,
            'permission' => 'collaborate.workflows.board',
            'defaults' => $defaults,
            'group' => $group,
            'users' => $users,
            'formWidth' => 'max-w-7xl',
            'langParams' => [
                'board' => $board->title,
                'type' => $taskType->name,
            ],
            'withValidation' => true,
            'taskTypeOptions' => $taskTypeOptions,
            'taskTypeWithoutCompleted' => $taskTypeWithoutCompleted,
        ]);
    }

    /**
     * Update the given task type defaults.
     *
     * @param TaskTypeDefaultsRequest $request
     * @param Board                   $board
     * @param TaskType                $taskType
     *
     * @return RedirectResponse
     */
    public function update(TaskTypeDefaultsRequest $request, Board $board, TaskType $taskType): RedirectResponse
    {
        /** @var array<int, mixed> $defaults */
        $defaults = $board->task_type_defaults ?? [];
        $defaults[$taskType->id] = array_merge($defaults[$taskType->id] ?? [], $request->validated());

        foreach ($defaults as $taskTypeId => $defs) {
            $defaults[$taskTypeId] = $this->stringsToNumerics($defs);
        }
        $board->update(['task_type_defaults' => $defaults]);

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.boards.show', $board->id);
    }
}
