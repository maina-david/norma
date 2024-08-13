<?php

namespace App\Rules\Workflows\Tasks;

use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Reference;
use App\Models\Workflows\Task;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AppliedIdentifiedCitations implements ValidationRule
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
            ->has('contentDraft')
            ->where('type', ReferenceType::citation()->value)
            ->doesntExist();

        if (!$passes) {
            $fail('workflows.task.validations.pending_identified_citations')->translate();
        }
    }
}
