<?php

namespace App\Http\Controllers\Api\Internals\My\Storage;

use App\Http\Resources\Internals\My\Storage\FileResource;
use App\Traits\Storage\HandlesOrganisationFiles;

class FileController
{
    use HandlesOrganisationFiles;

    /**
     * Store new files.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return \App\Http\Resources\Internals\My\Storage\FileResource
     */
    public function store(): FileResource
    {
        $processed = $this->handleUpload();

        // Filepond sends files one at a time.
        return new FileResource($processed['files'][0]);
    }
}
