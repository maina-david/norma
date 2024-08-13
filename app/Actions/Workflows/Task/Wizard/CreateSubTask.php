<?php

namespace App\Actions\Workflows\Task\Wizard;

use App\Contracts\Workflows\Task\WizardAction;
use App\Enums\Workflows\TaskStatus;
use App\Enums\Workflows\TaskTriggerType;
use App\Models\Storage\Collaborate\Attachment;
use App\Models\Workflows\Board;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskTrigger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateSubTask implements WizardAction
{
    use AsAction;

    protected Collection $created;

    protected Board $board;

    protected Task $parent;

    /**
     * {@inheritDoc}
     */
    public function handle(array $requestData, array $executed, array $meta = []): bool
    {
        $this->created = collect();
        $this->board = $meta['board'];
        $this->parent = $meta['parent'];

        $originalOrder = explode(',', $this->board->task_type_order ?? '');
        $order = $this->manageGhostTasks($originalOrder);
        $data = collect($requestData);

        foreach ($originalOrder as $index => $type) {
            // @codeCoverageIgnoreStart
            if (!in_array($type, $order)) {
                if ($index > 0) {
                    Task::where('task_type_id', $originalOrder[$index - 1])
                        ->where('parent_task_id', $this->parent->id)
                        ->where('document_id', $this->parent->document_id)
                        ->where('group_id', $this->parent->group_id)
                        ->where('board_id', $this->parent->board_id)
                        ->update(['requires_rating' => false]);
                }
                continue;
            }
            // @codeCoverageIgnoreEnd

            $forTask = $data->filter(fn ($val, $key) => str_starts_with($key, "type_{$type}_"))
                ->mapWithKeys(fn ($val, $key) => [str_replace("type_{$type}_", '', $key) => $val])
                ->toArray();

            $forTask['project_type'] = $meta['project_type'] ?? null;
            $created = $this->createSubTask($type, $forTask);
            $this->created->put($type, $created);
        }

        $this->created->first()?->update(['task_status' => TaskStatus::todo()->value]);

        return true;
    }

    /**
     * Manage the tasks that are created after a given number.
     *
     * @param array<array-key, string> $taskTypes
     *
     * @return array<array-key, string>
     */
    protected function manageGhostTasks(array $taskTypes): array
    {
        $updated = [];

        foreach ($taskTypes as $type) {
            $defaults = $this->board->task_type_defaults[$type] ?? false;
            $skip = $defaults['create_one_for_every'] ?? false;

            if (!$defaults || !$skip || $skip == 1) {
                $updated[] = $type;

                continue;
            }

            // @codeCoverageIgnoreStart
            $parents = Task::where('board_id', $this->board->id)
                ->whereNull('parent_task_id')
                ->orderBy('id', 'desc')
                ->take($skip - 1)
                ->get(['id'])
                ->map(fn ($t) => $t->id)
                ->toArray();

            if (!Task::whereIn('parent_task_id', $parents)->where('task_type_id', $type)->exists()) {
                $updated[] = $type;
            }
            // @codeCoverageIgnoreEnd
        }

        return $updated;
    }

    /**
     * @param string               $type
     * @param array<string, mixed> $taskDetails
     *
     * @return Task
     */
    protected function createSubTask(string $type, array $taskDetails): Task
    {
        $defaults = $this->board->task_type_defaults[$type] ?? [];
        $dependsOn = $this->created->get($defaults['depends_on'] ?? null);

        /** @var array<string, mixed> $payload */
        $payload = [
            ...$defaults,
            ...$taskDetails,
            'parent_task_id' => $this->parent->id,
            'document_id' => $this->parent->document_id,
            'group_id' => $this->parent->group_id,
            'board_id' => $this->parent->board_id,
            'project_id' => $this->parent->project_id,
            'task_type_id' => $type,
            'depends_on_task_id' => $dependsOn->id ?? null,
            'task_status' => TaskStatus::pending()->value,
        ];

        $payload['auto_payment'] = $payload['auto_payment'] ?? false;
        $payload['requires_rating'] = $payload['requires_rating'] ?? false;

        /** @var Task $created */
        $created = Task::create($payload);

        // @codeCoverageIgnoreStart
        if (isset($taskDetails['attachment'])) {
            $attachment = new Attachment();

            /** @var UploadedFile $file */
            $file = $taskDetails['attachment'];

            $attachment->saveFile($file);

            $created->attachments()->attach($attachment->id);
        }
        // @codeCoverageIgnoreEnd

        $ownTriggers = collect(['set_current_due_date', 'set_current_due_date_trigger', 'set_current_task_duration'])
            ->reduce(fn ($pre, $curr) => $pre && ($defaults[$curr] ?? false), true);

        if ($ownTriggers) {
            $this->handleCurrentDueDateTrigger($created, $defaults);
        }

        $ownTriggers = collect(['set_current_units', 'set_current_units_trigger', 'set_current_units_type'])
            ->reduce(fn ($pre, $curr) => $pre && ($defaults[$curr] ?? false), true);

        if ($ownTriggers) {
            $this->handleCurrentUnitsTrigger($created, $defaults);
        }

        if (!$dependsOn) {
            return $created;
        }

        $dependsOnDefaults = $this->board->task_type_defaults[$defaults['depends_on'] ?? false] ?? [];

        $moveTriggers = collect(['move_dependent_task', 'move_dependent_task_trigger', 'move_dependent_task_status'])
            ->reduce(fn ($pre, $curr) => $pre && ($dependsOnDefaults[$curr] ?? false), true);

        if ($moveTriggers) {
            $this->handleTaskStatusTrigger($created, $dependsOn, $dependsOnDefaults);
        }

        $dueTriggers = collect(['set_dependent_due_date', 'set_dependent_due_date_trigger', 'set_dependent_task_duration'])
            ->reduce(fn ($pre, $curr) => $pre && ($dependsOnDefaults[$curr] ?? false), true);

        if ($dueTriggers) {
            $this->handleDueDateTrigger($created, $dependsOn, $dependsOnDefaults);
        }

        return $created;
    }

    /**
     * Create the task status triggers.
     *
     * @param Task                 $created
     * @param Task                 $dependsOn
     * @param array<string, mixed> $typeDefaults
     *
     * @return void
     */
    private function handleTaskStatusTrigger(Task $created, Task $dependsOn, array $typeDefaults): void
    {
        TaskTrigger::create([
            'source_task_id' => $dependsOn->id,
            'target_task_id' => $created->id,
            'trigger_type' => TaskTriggerType::ON_STATUS_CHANGE->value,
            'details' => [
                'task_status' => $typeDefaults['move_dependent_task_trigger'],
                'update_field' => 'task_status',
                'update_field_value' => $typeDefaults['move_dependent_task_status'],
            ],
            'triggered' => false,
        ]);
    }

    /**
     * Set the task due date trigger.
     *
     * @param Task                 $created
     * @param Task                 $dependsOn
     * @param array<string, mixed> $typeDefaults
     *
     * @return void
     */
    private function handleDueDateTrigger(Task $created, Task $dependsOn, array $typeDefaults): void
    {
        TaskTrigger::create([
            'source_task_id' => $dependsOn->id,
            'target_task_id' => $created->id,
            'trigger_type' => TaskTriggerType::ON_STATUS_CHANGE->value,
            'details' => [
                'task_status' => $typeDefaults['set_dependent_due_date_trigger'],
                'update_field' => 'due_date',
                'update_field_value' => $typeDefaults['set_dependent_task_duration'],
            ],
            'triggered' => false,
        ]);
    }

    /**
     * Set the task due date trigger.
     *
     * @param Task                 $task
     * @param array<string, mixed> $typeDefaults
     *
     * @return void
     */
    private function handleCurrentUnitsTrigger(Task $task, array $typeDefaults): void
    {
        TaskTrigger::create([
            'source_task_id' => $task->id,
            'target_task_id' => $task->id,
            'trigger_type' => TaskTriggerType::ON_STATUS_CHANGE,
            'details' => [
                'task_status' => $typeDefaults['set_current_units_trigger'],
                'update_field' => 'units',
                'update_field_value' => $typeDefaults['set_current_units_type'],
            ],
            'triggered' => false,
        ]);
    }

    /**
     * Set the task due date trigger.
     *
     * @param Task                 $task
     * @param array<string, mixed> $typeDefaults
     *
     * @return void
     */
    private function handleCurrentDueDateTrigger(Task $task, array $typeDefaults): void
    {
        TaskTrigger::create([
            'source_task_id' => $task->id,
            'target_task_id' => $task->id,
            'trigger_type' => TaskTriggerType::ON_STATUS_CHANGE,
            'details' => [
                'task_status' => $typeDefaults['set_current_due_date_trigger'],
                'update_field' => 'due_date',
                'update_field_value' => $typeDefaults['set_current_task_duration'],
            ],
            'triggered' => false,
        ]);
    }
}
