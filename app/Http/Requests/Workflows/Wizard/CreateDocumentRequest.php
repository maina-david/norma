<?php

namespace App\Http\Requests\Workflows\Wizard;

use Illuminate\Foundation\Http\FormRequest;

class CreateDocumentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        return [
            'title' => ['string', 'required'],
        ];
    }
}
