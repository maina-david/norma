<?php

namespace App\Stores\Compilation;

use App\Models\Compilation\Library;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Database\Eloquent\Collection;

class LibraryLibraryStore
{
    use AttachesDetaches;

    /**
     * @param Library                 $library
     * @param Collection<Library|int> $children
     *
     * @return void
     */
    public function attachChildren(Library $library, Collection $children): void
    {
        $this->attachRelations($library, 'children', $children);
    }

    /**
     * @param Library                 $library
     * @param Collection<Library|int> $children
     *
     * @return void
     */
    public function detachChildren(Library $library, Collection $children): void
    {
        $this->detachRelations($library, 'children', $children);
    }

    /**
     * @param Library                 $library
     * @param Collection<Library|int> $parents
     *
     * @return void
     */
    public function attachParents(Library $library, Collection $parents): void
    {
        $this->attachRelations($library, 'parents', $parents);
    }

    /**
     * @param Library                 $library
     * @param Collection<Library|int> $parents
     *
     * @return void
     */
    public function detachParents(Library $library, Collection $parents): void
    {
        $this->detachRelations($library, 'parents', $parents);
    }
}
