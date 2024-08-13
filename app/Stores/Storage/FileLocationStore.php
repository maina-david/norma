<?php

namespace App\Stores\Storage;

use App\Models\Geonames\Location;
use App\Models\Storage\My\File;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Support\Collection;

class FileLocationStore
{
    use AttachesDetaches;

    /**
     * Attach the locations to the file.
     *
     * @param File                      $file
     * @param Collection<int, Location> $domains
     *
     * @return File
     */
    public function attachLocations(File $file, Collection $domains): File
    {
        $this->attachRelations($file, 'locations', $domains);

        return $file;
    }

    /**
     * Attach the locations to the file.
     *
     * @param File                      $file
     * @param Collection<int, Location> $domains
     *
     * @return File
     */
    public function detachLocations(File $file, Collection $domains): File
    {
        $this->detachRelations($file, 'locations', $domains);

        return $file;
    }
}
