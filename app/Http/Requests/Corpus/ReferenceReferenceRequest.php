<?php

namespace App\Http\Requests\Corpus;

use Illuminate\Foundation\Http\FormRequest;

class ReferenceReferenceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'link_type' => ['required', 'integer'],
            'references' => ['required'],
            'references.*' => ['required', 'integer'],
        ];
    }
}
