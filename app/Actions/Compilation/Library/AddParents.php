<?php

namespace App\Actions\Compilation\Library;

use App\Models\Compilation\Library;
use App\Models\Customer\Organisation;
use App\Stores\Compilation\LibraryLibraryStore;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class AddParents
{
    use AsAction;

    /**
     * @param LibraryLibraryStore $libraryLibraryStore
     */
    public function __construct(protected LibraryLibraryStore $libraryLibraryStore)
    {
    }

    /**
     * @param Library           $library
     * @param array<int>        $libraryIds
     * @param Organisation|null $organisation
     *
     * @return void
     */
    public function handle(Library $library, array $libraryIds, ?Organisation $organisation): void
    {
        $query = is_null($organisation)
            ? (new Library())->newQuery()
            : Library::forOrganisation($organisation->id);

        /** @var Collection<Library> */
        $libraries = $query->whereKey($libraryIds)
            ->get(['id']);

        $this->libraryLibraryStore->attachParents($library, $libraries);
    }
}
