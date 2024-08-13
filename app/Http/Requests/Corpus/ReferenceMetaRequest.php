<?php

namespace App\Http\Requests\Corpus;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReferenceMetaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        return [
            'related' => [
                Rule::requiredIf(!$this->has('_delete_target')), 'array',
            ],
            'related.*' => ['integer'],
        ];
    }
}
