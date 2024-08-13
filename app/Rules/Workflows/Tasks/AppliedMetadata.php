<?php

namespace App\Rules\Workflows\Tasks;

use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Reference;
use App\Models\Workflows\Task;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AppliedMetadata implements ValidationRule
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
            ->where(function ($query) {
                $query->has('assessmentItemDrafts')
                    ->orHas('categoryDrafts')
                    ->orHas('contextQuestionDrafts')
                    ->orHas('legalDomainDrafts')
                    ->orHas('locationDrafts')
                    ->orHas('tagDrafts');
            })
            ->doesntExist();

        if (!$passes) {
            $fail('workflows.task.validations.pending_metadata')->translate();
        }
    }
}
