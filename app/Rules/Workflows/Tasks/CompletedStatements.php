<?php

namespace App\Rules\Workflows\Tasks;

use App\Models\Workflows\Task;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * This is a placeholder in case we need it.
 *
 * @codeCoverageIgnore
 */
class CompletedStatements implements ValidationRule
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
    }
}
