<?php

namespace App\Http\Requests\Workflows\Wizard;

use App\Models\Workflows\Board;
use Illuminate\Foundation\Http\FormRequest;

class TaskDetailsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        /** @var int $board */
        $board = $this->route('board');
        /** @var Board $board */
        $board = Board::cached($board);

        $computed = [];

        $rules = [
            'attachment' => ['nullable', 'file'],
            'auto_payment' => ['nullable', 'boolean'],
            'description' => ['nullable'],
            'due_date' => ['nullable'],
            'group_id' => ['nullable'],
            'manager_id' => ['nullable'],
            'priority' => ['nullable'],
            'requires_rating' => ['nullable', 'boolean'],
            'title' => ['nullable'],
            'user_id' => ['nullable'],
            'complexity' => ['nullable', 'numeric'],
            'units' => ['nullable', 'numeric'],
        ];

        /** @var array<array-key, int> $defaults */
        $defaults = $board->task_type_order ? explode(',', $board->task_type_order) : [];

        $rules = collect($defaults)
            ->reduce(function ($pre, $cur) use ($rules) {
                return $pre->merge(collect($rules)->mapWithKeys(fn ($val, $key) => ["type_{$cur}_{$key}" => $val]));
            }, collect())
            ->toArray();

        return $rules;
    }
}
