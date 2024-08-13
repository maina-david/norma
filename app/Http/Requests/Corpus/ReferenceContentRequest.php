<?php

namespace App\Http\Requests\Corpus;

use Illuminate\Foundation\Http\FormRequest;

class ReferenceContentRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'request_requirement' => $this->has('request_requirement'),
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
            'title' => ['string', 'required'],
            'content' => ['string', 'nullable'],
            'request_requirement' => ['boolean'],
        ];
    }
}
