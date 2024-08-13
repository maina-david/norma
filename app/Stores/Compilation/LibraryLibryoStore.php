<?php

namespace App\Stores\Compilation;

use App\Models\Compilation\Library;
use App\Models\Customer\Libryo;

class LibraryLibryoStore
{
    /**
     * Create a new Library for the given libryo.
     *
     * @param Libryo $libryo
     *
     * @return Library
     */
    public function createForLibryo(Libryo $libryo): Library
    {
        /** @var Library */
        return Library::create([
            'title' => $libryo->title . ' - Embryo',
            'latitude' => $libryo->geo_lat,
            'longitude' => $libryo->geo_lng,
            'location_id' => $libryo->location_id,
            'auto_compiled' => 1,
        ]);
    }
}
