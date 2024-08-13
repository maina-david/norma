<?php

namespace App\Http\Requests\Workflows;

use Illuminate\Foundation\Http\FormRequest;

class TaskTypeDefaultsRequest extends FormRequest
{
    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        if (!$this->has('set_dependent_due_date')) {
            $this->merge([
                'set_dependent_due_date_trigger' => null,
                'set_dependent_task_duration' => null,
            ]);
        }

        if (!$this->has('set_current_due_date')) {
            $this->merge([
                'set_current_due_date_trigger' => null,
                'set_current_task_duration' => null,
            ]);
        }

        if (!$this->has('set_current_units')) {
            $this->merge([
                'set_current_units_trigger' => null,
                'set_current_units_type' => null,
                'set_current_units_multiple' => null,
            ]);
        }

        if (!$this->has('publish_related_work')) {
            $this->merge([
                'publish_related_work_trigger' => null,
            ]);
        }

        if (!$this->has('generate_reference_extracts')) {
            $this->merge([
                'generate_reference_extracts_trigger' => null,
            ]);
        }

        if (!$this->has('cache_related_work')) {
            $this->merge([
                'cache_related_work_trigger' => null,
            ]);
        }

        if (!$this->has('move_dependent_task')) {
            $this->merge([
                'move_dependent_task_status' => null,
                'move_dependent_task_trigger' => null,
            ]);
        }

        $this->merge([
            'auto_payment' => $this->has('auto_payment'),
            'move_dependent_task' => $this->has('move_dependent_task'),
            'publish_related_work' => $this->has('publish_related_work'),
            'generate_reference_extracts' => $this->has('generate_reference_extracts'),
            'cache_related_work' => $this->has('cache_related_work'),
            'requires_rating' => $this->has('requires_rating'),
            'set_current_due_date' => $this->has('set_current_due_date'),
            'set_current_units' => $this->has('set_current_units'),
            'set_dependent_complexity' => $this->has('set_dependent_complexity'),
            'set_dependent_due_date' => $this->has('set_dependent_due_date'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,mixed> $rules
     */
    public function rules(): array
    {
        return [
            'attachments' => ['nullable'],
            'auto_payment' => ['nullable', 'boolean'],
            'complexity' => ['nullable'],
            'create_one_for_every' => ['nullable'],
            'depends_on' => ['nullable'],
            'description' => ['nullable'],
            'due_date' => ['nullable'],
            'group_id' => ['nullable'],
            'manager_id' => ['nullable'],
            'move_dependent_task' => ['nullable', 'boolean'],
            'move_dependent_task_status' => ['nullable'],
            'move_dependent_task_trigger' => ['nullable'],
            'priority' => ['nullable'],
            'cache_related_work' => ['nullable', 'boolean'],
            'cache_related_work_trigger' => ['nullable'],
            'publish_related_work' => ['nullable', 'boolean'],
            'publish_related_work_trigger' => ['nullable'],
            'generate_reference_extracts' => ['nullable', 'boolean'],
            'generate_reference_extracts_trigger' => ['nullable'],
            'requires_rating' => ['nullable', 'boolean'],
            'set_current_due_date' => ['nullable', 'boolean'],
            'set_current_due_date_trigger' => ['nullable'],
            'set_current_task_duration' => ['nullable'],
            'set_dependent_complexity' => ['nullable', 'boolean'],
            'set_dependent_due_date' => ['nullable', 'boolean'],
            'set_dependent_due_date_trigger' => ['nullable'],
            'set_dependent_task_duration' => ['nullable'],
            'set_dependent_units_multiple' => ['nullable', 'numeric'],
            'set_dependent_units_type' => ['nullable'],
            'set_current_units' => ['nullable', 'boolean'],
            'set_current_units_trigger' => ['nullable'],
            'set_current_units_type' => ['nullable'],
            'set_current_units_multiple' => ['nullable', 'numeric'],
            'task_status' => ['nullable'],
            'task_type_id' => ['nullable'],
            'title' => ['nullable'],
            'units' => ['nullable'],
            'user_id' => ['nullable'],
            'validations' => ['nullable', 'array'],
            'validations.status' => ['nullable', 'array'],
            'validations.on_todo' => ['nullable', 'array'],
            'validations.status.complexity' => ['numeric'],
            'validations.status.statements' => ['nullable', 'numeric'],
            'validations.status.citations' => ['nullable', 'numeric'],
            'validations.status.identified_citations' => ['nullable', 'numeric'],
            'validations.status.metadata' => ['nullable', 'numeric'],
            'validations.status.summaries' => ['nullable', 'numeric'],
            'validations.status.work_highlights' => ['nullable', 'numeric'],
            'validations.status.work_update_report' => ['nullable', 'numeric'],
            'validations.status.work' => ['nullable', 'numeric'],
            'validations.status.work_expression' => ['nullable', 'numeric'],
            'validations.on_todo.auto_archive' => ['nullable', 'numeric'],
        ];
    }
}
