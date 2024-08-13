<?php

namespace App\Http\Requests\Arachno;

use App\Models\Ontology\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property Tag $tag
 */
class TagRequest extends FormRequest
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
     * @return array<string, mixed> $rules
     */
    public function rules()
    {
        return [
            'title' => [
                'string', 'required', 'max:255',
                Rule::unique(Tag::class)->ignore($this->tag, 'id'),
            ],
        ];
    }
}
