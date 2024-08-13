<?php

namespace App\Rules\Workflows\Tasks;

use App\Models\Notify\LegalUpdate;
use App\Models\Workflows\Task;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CompletedLegalUpdateHighlights implements ValidationRule
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
            ->whereRaw('length(coalesce(highlights, "")) < 20')
            ->doesntExist();

        if (!$passes) {
            $fail('workflows.task.validations.incomplete_highlights')->translate();
        }
    }
}
