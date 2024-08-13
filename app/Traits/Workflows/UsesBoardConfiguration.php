<?php

namespace App\Traits\Workflows;

use App\Models\Auth\User;
use App\Models\Workflows\Board;
use App\Models\Workflows\TaskType;
use Illuminate\Support\Collection;

trait UsesBoardConfiguration
{
    /**
     * Get the task types that the current one can depend on.
     *
     * @param Board    $board
     * @param TaskType $taskType
     *
     * @return array<array-key, array<string, string|int>>
     */
    protected function getDependableTypes(Board $board, TaskType $taskType): array
    {
        /** @var Collection<string|int> $order */
        $order = collect(explode(',', $board->task_type_order ?? ''));

        $index = $order->search($taskType->id);

        abort_if($index === false, 404);

        /** @var int $index */
        /** @var Collection<TaskType> $types */
        $types = TaskType::find($order->slice(0, $index), ['id', 'name']);
        $types = $types->keyBy('id');

        $types = $order->slice(0, $index)
            ->map(function ($id) use ($types) {
                $type = $types->get($id);

                return $type ? ['value' => $type->id, 'label' => $type->name] : null;
            })
            ->filter();

        $types->prepend(['value' => '', 'label' => __('workflows.task.no_task')]);

        return $types->toArray();
    }

    /**
     * Get the users attached to the board defaults.
     *
     * @param object $defaults
     *
     * @return Collection<User>
     */
    protected function getUsers(object $defaults): Collection
    {
        $users = collect(['user_id', 'manager_id'])->map(fn ($field) => $defaults->{$field} ?? null)->filter();

        if ($users->isNotEmpty()) {
            /** @var Collection<User> $users */
            $users = User::find($users->values()->toArray(), ['id', 'fname', 'sname']);
            $users = $users->keyBy('id');
        }

        return $users;
    }

    /**
     * @param array<string, mixed> $defaults
     *
     * @return array<string, mixed>
     */
    private function stringsToNumerics(array $defaults): array
    {
        $keys = [
            'priority',
            'depends_on',
            'manager_id',
            'group_id',
            'cache_related_work_trigger',
            'move_dependent_task_status',
            'move_dependent_task_trigger',
            'publish_related_work_trigger',
            'generate_reference_extracts_trigger',
            'set_current_due_date_trigger',
            'set_dependent_due_date_trigger',
            'set_dependent_units_type',
            'set_current_units_trigger',
            'set_current_units_type',
            'task_status',
            'task_type_id',
            'user_id',
        ];
        foreach ($keys as $key) {
            if (isset($defaults[$key]) && is_numeric($defaults[$key])) {
                $defaults[$key] = (int) $defaults[$key];
            }
        }

        $keys = ['set_dependent_units_multiple', 'set_current_units_multiple'];
        foreach ($keys as $key) {
            if (isset($defaults[$key]) && is_numeric($defaults[$key])) {
                $defaults[$key] = (float) $defaults[$key];
            }
        }

        return $defaults;
    }
}
