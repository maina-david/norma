<?php

namespace App\Rules\Workflows\Tasks;

use App\Enums\Corpus\ReferenceStatus;
use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Reference;
use App\Models\Workflows\Task;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AppliedCitations implements ValidationRule
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
        $passes = Reference::where('work_id', $this->task->document->expression->work_id ?? null)
            ->where('type', ReferenceType::citation()->value)
            ->where('status', ReferenceStatus::pending()->value)
            ->doesntExist();

        if (!$passes) {
            $fail('workflows.task.validations.pending_citations')->translate();
        }
    }
}
