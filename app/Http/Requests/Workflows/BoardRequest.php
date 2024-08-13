<?php

namespace App\Http\Requests\Workflows;

use App\Models\Workflows\Board;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BoardRequest extends FormRequest
{
    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'for_legal_update' => $this->has('for_legal_update'),
            'source_document_required' => $this->has('source_document_required'),
            'wizard_steps' => $this->has('wizard_steps') ? explode(',', $this->get('wizard_steps')) : null,
            'optional_wizard_steps' => $this->get('optional_wizard_steps', []),
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
            'title' => ['required', Rule::unique(Board::class, 'title')->ignore($this->route('board'))],
            'parent_task_type_id' => ['required', 'numeric'],
            'task_type_order' => ['nullable', 'string'],
            'for_legal_update' => ['boolean'],
            'source_document_required' => ['boolean'],
            'task_type_defaults' => [],
            'wizard_steps' => ['required', 'array'],
            'optional_wizard_steps' => ['nullable', 'array'],
        ];
    }
}
