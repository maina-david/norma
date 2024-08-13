<?php

namespace App\Rules\Workflows\Tasks;

use App\Models\Workflows\Task;
use App\Models\Workflows\TaskType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AppliedComplexity implements ValidationRule
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
        $this->task->load(['board']);

        $order = explode(',', $this->task->board->task_type_order ?? '');
        $index = array_search($this->task->task_type_id, $order);

        if ($index === false || !isset($order[$index + 1])) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $passes = $this->task->parent()
            ->whereHas('childTasks', function ($builder) use ($index, $order) {
                $builder
                    ->where('task_type_id', $order[$index + 1])
                    ->where(fn ($q) => $q->whereNull('complexity')->orWhere('complexity', 0));
            })
            ->doesntExist();

        if (!$passes) {
            $this->task->load(['board']);

            $order = explode(',', $this->task->board->task_type_order ?? '');
            $index = array_search($this->task->task_type_id, $order);

            /** @var TaskType|null $taskType */
            $taskType = TaskType::whereKey($order[$index + 1])->first();
            $taskType = $taskType->name ?? 'Sibling';

            $fail('workflows.task.validations.missing_complexity')->translate(['task' => $taskType]);
        }
    }
}
