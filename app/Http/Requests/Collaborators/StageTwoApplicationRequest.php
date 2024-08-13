<?php

namespace App\Http\Requests\Collaborators;

use App\Services\Storage\MimeTypeManager;
use Illuminate\Foundation\Http\FormRequest;

class StageTwoApplicationRequest extends FormRequest
{
    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'process_personal_data' => $this->has('process_personal_data'),
            'permission_to_work' => $this->has('permission_to_work'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules()
    {
        $mimes = implode(',', (new MimeTypeManager())->getAcceptedUploadMimes());

        return [
            'address_line_1' => ['required', 'string', 'max:255'],
            'postal_address' => ['nullable', 'string', 'max:255'],
            'identity' => ['required', 'file', 'max:51200', "mimetypes:{$mimes}"],
            'visa' => ['nullable', 'file', 'max:51200', "mimetypes:{$mimes}"],
            'process_personal_data' => ['boolean', 'required'],
            'permission_to_work' => ['boolean', 'required'],
        ];
    }
}
