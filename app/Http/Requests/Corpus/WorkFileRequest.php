<?php

namespace App\Http\Requests\Corpus;

use App\Services\Storage\MimeTypeManager;
use Illuminate\Foundation\Http\FormRequest;

class WorkFileRequest extends FormRequest
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

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    protected function getRedirectUrl()
    {
        return route('collaborate.document-editor.file-browser.show', ['expression' => $this->route('expression')]);
    }
}
