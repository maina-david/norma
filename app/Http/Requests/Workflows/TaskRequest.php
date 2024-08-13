<?php

namespace App\Http\Requests\Workflows;

use App\Models\Auth\User;
use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'auto_payment' => $this->has('auto_payment'),
            'requires_rating' => $this->has('requires_rating'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,mixed> $rules
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        $rules = [
            'auto_payment' => ['nullable', 'boolean'],
            'description' => ['nullable'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'group_id' => ['nullable'],
            'manager_id' => ['nullable'],
            'priority' => ['nullable'],
            'requires_rating' => ['nullable', 'boolean'],
            'title' => ['nullable'],
            'user_id' => ['nullable'],
        ];

        if ($user->can('collaborate.workflows.task.set-complexity')) {
            $rules['complexity'] = ['nullable', 'numeric'];
        }

        if ($user->can('collaborate.workflows.task.set-units')) {
            $rules['units'] = ['nullable', 'numeric'];
        }

        return $rules;
    }
}
