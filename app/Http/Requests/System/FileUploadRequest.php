<?php

namespace App\Http\Requests\System;

use App\Traits\FileUploadRules;
use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
{
    use FileUploadRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,mixed>
     */
    public function rules()
    {
        $rules = $this->fileRules();

        return [
            'file' => ['nullable', ...$rules],
            'files' => ['nullable', 'array'],
            'files.*' => ['nullable', ...$rules],
        ];
    }
}
