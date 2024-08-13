<?php

namespace App\Http\Requests\Workflows;

use App\Enums\Workflows\OnCompleteValidationType;
use App\Enums\Workflows\TaskStatus;
use App\Models\Workflows\Task;
use App\Rules\Workflows\Tasks\DependentClosed;
use App\Rules\Workflows\Tasks\RequiresRating;
use App\Rules\Workflows\Tasks\RequiresUnits;
use Illuminate\Foundation\Http\FormRequest;

class TaskStatusRequest extends FormRequest
{
    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'new_task_status' => $this->route('status'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        $rules = [];

        /** @var string $status */
        $status = $this->route('status');
        $status = (int) $status;

        if (TaskStatus::inReview()->is($status) || TaskStatus::done()->is($status)) {
            /** @var Task $task */
            $task = Task::whereKey($this->route('task'))->firstOrFail();
            $task->setAttribute('target_status', $status);

            $rules = OnCompleteValidationType::forTask($task);

            $rules[] = new DependentClosed($task);

            if (TaskStatus::done()->is($status)) {
                $rules[] = new RequiresRating($task);
                $rules[] = new RequiresUnits($task);
            }
        }

        return [
            'new_task_status' => $rules,
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getRedirectUrl()
    {
        return route('collaborate.tasks.show', $this->route('task'));
    }
}
