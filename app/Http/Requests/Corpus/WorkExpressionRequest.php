<?php

namespace App\Http\Requests\Corpus;

use Illuminate\Foundation\Http\FormRequest;

class WorkExpressionRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'show_source_document' => $this->has('show_source_document'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['nullable', 'file'],
            'start_date' => ['required', 'date'],
            'source_url' => ['nullable', 'url'],
            'show_source_document' => ['boolean'],
        ];
    }
}
