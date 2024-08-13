<?php

namespace App\Http\Requests\Compilation;

use App\Enums\Compilation\ContextQuestionCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContextQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'prefix' => ['nullable', 'string', 'max:255'],
            'predicate' => ['nullable', 'string', 'max:255'],
            'pre_object' => ['nullable', 'string', 'max:255'],
            'object' => ['nullable', 'string', 'max:255'],
            'post_object' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'numeric', Rule::in(ContextQuestionCategory::options())],
        ];
    }
}
