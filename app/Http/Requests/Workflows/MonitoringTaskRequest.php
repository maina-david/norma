<?php

namespace App\Http\Requests\Workflows;

use App\Models\Workflows\Board;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MonitoringTaskRequest extends FormRequest
{
    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'enabled' => $this->has('enabled'),
            'locations' => collect($this->get('locations'))->map(fn ($item) => (int) $item)->all(),
            'legal_domains' => collect($this->get('legal_domains'))->map(fn ($item) => (int) $item)->all(),
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
            'board_id' => ['required', 'numeric'],
            'group_id' => ['required', 'numeric'],
            'start_day' => ['required', 'numeric'],
            'frequency' => ['required', 'numeric', 'min:1'],
            'frequency_quantity' => ['required', 'numeric'],
            'priority' => ['required', 'numeric'],
            'locations' => ['required', 'array'],
            'legal_domains' => ['required', 'array'],
            'last_run' => ['required', 'date'],
            'enabled' => ['required', 'bool'],
        ];
    }
}
