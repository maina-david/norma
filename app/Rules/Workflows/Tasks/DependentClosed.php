<?php

namespace App\Rules\Workflows\Tasks;

use App\Enums\Workflows\TaskStatus;
use App\Models\Workflows\Task;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DependentClosed implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(protected Task $task)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $this->task->load(['dependsOn']);
        $allowedToTransition = [TaskStatus::done()->value, TaskStatus::archive()->value];

        $passes = !$this->task->dependsOn || in_array($this->task->dependsOn->task_status, $allowedToTransition);

        if (!$passes) {
            // @codeCoverageIgnoreStart
            $fail('workflows.task.validations.dependent_task_in_progress')->translate([
                'type' => $this->task->dependsOn->taskType->name ?? '',
            ]);
            // @codeCoverageIgnoreEnd
        }
    }
}
