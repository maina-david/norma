<?php

namespace App\Http\Requests\Workflows;

use App\Models\Workflows\RatingGroup;
use App\Models\Workflows\TaskRoute;
use App\Models\Workflows\TaskType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskTypeRequest extends FormRequest
{
    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'is_parent' => $this->has('is_parent'),
            'assignee_can_close' => $this->has('assignee_can_close'),
            'has_checks' => $this->has('has_checks'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', Rule::unique(TaskType::class, 'name')->ignore($this->route('task_type'))->withoutTrashed()],
            'colour' => ['required', 'string'],
            'course_link' => ['nullable', 'url'],
            'rating_group_id' => ['nullable', Rule::exists(RatingGroup::class, 'id')],
            'task_route_id' => ['required', Rule::exists(TaskRoute::class, 'id')],
            'is_parent' => ['boolean', 'required'],
            'assignee_can_close' => ['boolean', 'required'],
            'has_checks' => ['boolean', 'required'],
        ];
    }
}
