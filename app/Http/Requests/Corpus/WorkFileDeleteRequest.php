<?php

namespace App\Http\Requests\Corpus;

use Illuminate\Foundation\Http\FormRequest;

class WorkFileDeleteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'string'],
        ];
    }
}
