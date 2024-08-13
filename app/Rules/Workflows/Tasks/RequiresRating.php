<?php

namespace App\Rules\Workflows\Tasks;

use App\Models\Workflows\Task;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RequiresRating implements ValidationRule
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
        $passes = !$this->task->requires_rating || $this->task->isRated();

        if (!$passes) {
            $fail('workflows.task.validations.task_requires_rating')->translate();
        }
    }
}
