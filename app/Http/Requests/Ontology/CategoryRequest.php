<?php

namespace App\Http\Requests\Ontology;

use App\Models\Ontology\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
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
            'label' => ['required', 'string', 'max:255', Rule::unique(Category::class, 'label')->ignore($this->route('category'))],
            'display_label' => ['required', 'string', 'max:255', Rule::unique(Category::class, 'display_label')->ignore($this->route('category'))],
            'category_type_id' => ['required', 'numeric'],
            'parent_id' => ['numeric'],
        ];
    }
}
