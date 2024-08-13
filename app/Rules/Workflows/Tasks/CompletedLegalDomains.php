<?php

namespace App\Rules\Workflows\Tasks;

use App\Models\Corpus\Reference;
use App\Models\Workflows\Task;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CompletedLegalDomains implements ValidationRule
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
        $failed = Reference::where('work_id', $this->task->document->expression->work_id ?? null)
            ->doesntHave('legalDomains')
            ->doesntHave('legalDomainDrafts')
            ->exists();

        if ($failed) {
            $fail('workflows.task.validations.incomplete_legal_domains')->translate();
        }
    }
}
