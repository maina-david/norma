<?php

namespace App\Http\Requests\Traits;

use App\Services\Storage\MimeTypeManager;

trait HasExcelImport
{
    /**
     * Get the import rules that are to be used for excel imports.
     *
     * @return string[]
     */
    protected function excelImportRules(): array
    {
        $mimes = implode(',', app(MimeTypeManager::class)->getAcceptedExcelMimes());

        return [
            'required',
            'file',
            'max:5000',
            "mimetypes:{$mimes}",
        ];
    }
}
