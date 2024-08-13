<?php

namespace App\Rules\Workflows\Tasks;

use App\Enums\Notify\LegalUpdatePublishedStatus;
use App\Models\Notify\LegalUpdate;
use App\Models\Workflows\Task;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ApprovedLegalUpdate implements ValidationRule
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
            ->where('status', LegalUpdatePublishedStatus::UNPUBLISHED->value)
            ->doesntExist();

        if (!$passes) {
            $fail('workflows.task.validations.pending_legal_update')->translate();
        }
    }
}
