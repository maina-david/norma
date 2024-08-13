<?php

namespace App\Http\Requests\Actions;

use App\Models\Actions\ActionArea;
use App\Models\Ontology\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActionAreaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'control_category_id' => [
                'required',
                'integer',
                Rule::exists(Category::class, 'id'),
                Rule::unique(ActionArea::class, 'control_category_id')
                    ->where('subject_category_id', $this->get('subject_category_id'))
                    ->whereNull('deleted_at'),
            ],
            'subject_category_id' => ['required', 'integer', Rule::exists(Category::class, 'id')],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @codeCoverageIgnore
     *
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'control_category_id.unique' => __('validation.unique', ['attribute' => 'action area']),
        ];
    }
}
