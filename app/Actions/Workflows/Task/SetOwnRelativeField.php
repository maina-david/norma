<?php

namespace App\Actions\Workflows\Task;

use App\Contracts\Workflows\Task\TaskAction;
use App\Enums\Corpus\ReferenceType;
use App\Enums\Workflows\RelativeTaskAction;
use App\Models\Corpus\Reference;
use App\Models\Workflows\Task;

class SetOwnRelativeField implements TaskAction
{
    /**
     * The field to update.
     *
     * @var string
     */
    protected string $field = 'units';

    /**
     * The property to read the multiplier and custom value.
     *
     * @var string
     */
    protected string $fieldMultiple = 'set_dependent_units_multiple';

    /**
     * Switch the relative fields.
     *
     * @param string $field
     * @param string $multiple
     *
     * @return self
     */
    public function usingField(string $field, string $multiple): self
    {
        $this->field = $field;
        $this->fieldMultiple = $multiple;

        return $this;
    }

    /**
     * Execute the task action.
     *
     * @param Task       $task
     * @param mixed|null $payload
     *
     * @return void
     */
    public function handle(Task $task, mixed $payload = null): void
    {
        $task->load(['board']);
        $defaults = $task->board?->task_type_defaults[$task->task_type_id] ?? null;

        $task->update([$this->field => $this->getUnits($task, $payload, $defaults)]);
    }

    /**
     * Get the units to be applied to the dependent task.
     *
     * @param Task                  $task
     * @param mixed                 $payload
     * @param array<string, string> $defaults
     *
     * @return float
     */
    protected function getUnits(Task $task, mixed $payload, array $defaults): float
    {
        $multiple = $defaults[$this->fieldMultiple] ?? null;

        return match (RelativeTaskAction::tryFrom($payload)) {
            RelativeTaskAction::MULTIPLE_OF => $task->{$this->field} * ($multiple ?? 1),
            RelativeTaskAction::CUSTOM_VALUE => $multiple ?? $task->{$this->field},
            RelativeTaskAction::REFERENCES => $this->onReferences($task, $multiple ? (float) $multiple : null),
            default => $task->{$this->field},
        };
    }

    /**
     * Get the rate for the references.
     *
     * @param Task       $task
     * @param float|null $multiple
     *
     * @return float
     */
    protected function onReferences(Task $task, ?float $multiple): float
    {
        $task->load(['document.expression']);

        $count = Reference::where('work_id', $task->document->expression->work_id ?? null)
            ->where('type', ReferenceType::citation()->value)
            ->count();

        return $count * ($multiple ?? 1);
    }
}
