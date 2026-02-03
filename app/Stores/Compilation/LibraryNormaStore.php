<?php

namespace App\Stores\Compilation;

use App\Models\Compilation\Library;
use App\Models\Customer\Norma;

class LibraryNormaStore
{
    /**
     * Create a new Library for the given norma.
     *
     * @param Norma $norma
     *
     * @return Library
     */
    public function createForNorma(Norma $norma): Library
    {
        /** @var Library */
        return Library::create([
            'title' => $norma->title . ' - Embryo',
            'latitude' => $norma->geo_lat,
            'longitude' => $norma->geo_lng,
            'location_id' => $norma->location_id,
            'auto_compiled' => 1,
        ]);
    }
}
