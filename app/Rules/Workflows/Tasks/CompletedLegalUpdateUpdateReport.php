<?php

namespace App\Rules\Workflows\Tasks;

use App\Models\Notify\LegalUpdate;
use App\Models\Workflows\Task;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CompletedLegalUpdateUpdateReport implements ValidationRule
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
        $passes = LegalUpdate::whereKey($this->task->document->legal_update_id ?? null)
            ->whereRaw('length(coalesce(update_report, "")) < 20')
            ->doesntExist();

        if (!$passes) {
            $fail('workflows.task.validations.incomplete_update_report')->translate();
        }
    }
}
