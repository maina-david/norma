<?php

namespace App\Traits;

use App\Rules\System\Uploads\VirusScan;

trait FileUploadRules
{
    /**
     * Get the runes for the file uploads.
     *
     * @return array<int, mixed>
     */
    protected function fileRules(): array
    {
        $scan = app(VirusScan::class);

        return ['file', $scan];
    }
}
