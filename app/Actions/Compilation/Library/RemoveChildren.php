<?php

namespace App\Actions\Compilation\Library;

use App\Models\Compilation\Library;
use App\Stores\Compilation\LibraryLibraryStore;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveChildren
{
    use AsAction;

    /**
     * @param LibraryLibraryStore $libraryLibraryStore
     */
    public function __construct(protected LibraryLibraryStore $libraryLibraryStore)
    {
    }

    /**
     * @param Library             $library
     * @param Collection<Library> $libraries
     *
     * @return void
     */
    public function handle(Library $library, Collection $libraries): void
    {
        $this->libraryLibraryStore->detachChildren($library, $libraries);
    }
}
