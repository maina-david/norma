<?php

namespace App\Rules\Workflows\Tasks;

use App\Models\Workflows\Task;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RequiresUnits implements ValidationRule
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
        $passes = !$this->task->auto_payment || (!is_null($this->task->units) && $this->task->units > 0);

        if (!$passes) {
            // @codeCoverageIgnoreStart
            $fail('workflows.task.validations.task_requires_units')->translate();
            // @codeCoverageIgnoreEnd
        }
    }
}
