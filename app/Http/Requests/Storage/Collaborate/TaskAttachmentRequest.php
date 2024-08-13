<?php

namespace App\Http\Requests\Storage\Collaborate;

use App\Services\Storage\MimeTypeManager;
use Illuminate\Foundation\Http\FormRequest;

class TaskAttachmentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules(): array
    {
        $mimes = implode(',', (new MimeTypeManager())->getAcceptedUploadMimes());

        return [
            'file' => ['required', 'file', 'max:51200', "mimetypes:{$mimes}"],
        ];
    }
}
