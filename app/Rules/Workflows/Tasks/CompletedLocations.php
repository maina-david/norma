<?php

namespace App\Rules\Workflows\Tasks;

use App\Models\Corpus\Reference;
use App\Models\Workflows\Task;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CompletedLocations implements ValidationRule
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
            ->doesntHave('locations')
            ->doesntHave('locationDrafts')
            ->exists();

        if ($failed) {
            $fail('workflows.task.validations.incomplete_locations')->translate();
        }
    }
}
