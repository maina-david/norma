<?php

namespace App\Rules\Workflows\Tasks;

use App\Enums\Corpus\WorkStatus;
use App\Models\Corpus\Work;
use App\Models\Workflows\Task;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class WorkNotPending implements ValidationRule
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
            ->where('status', WorkStatus::pending()->value)
            ->doesntExist();

        if (!$passes) {
            $fail('workflows.task.validations.pending_work')->translate();
        }
    }
}
