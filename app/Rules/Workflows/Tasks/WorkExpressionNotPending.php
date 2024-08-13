<?php

namespace App\Rules\Workflows\Tasks;

use App\Models\Corpus\Work;
use App\Models\Workflows\Task;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class WorkExpressionNotPending implements ValidationRule
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
        $passes = Work::whereKey($this->task->document->expression->work_id ?? null)
            ->where('active_work_expression_id', $this->task->document->work_expression_id ?? null)
            ->exists();

        if (!$passes) {
            $fail('workflows.task.validations.pending_work_expression')->translate();
        }
    }
}
