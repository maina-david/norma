<?php

namespace App\Actions\Compilation\Library;

use App\Models\Compilation\Library;
use App\Models\Corpus\Reference;
use App\Models\Geonames\Location;
use App\Stores\Compilation\LibraryReferenceStore;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Generic compilation either adds (compiles) or removes (decompiles) references to a library by location and legal domains.
 * If includeChildLocations is true, then it will also find references tagged with the descendant locations.
 */
class GenericCompilation
{
    use AsAction;

    public string $jobQueue = 'compilation';

    public function __construct(protected LibraryReferenceStore $libraryReferenceStore)
    {
    }

    /**
     * @param int        $libraryId
     * @param int        $locationId
     * @param bool       $includeChildLocations
     * @param array<int> $domains
     * @param string     $action
     *
     * @return void
     */
    public function handle(
        int $libraryId,
        int $locationId,
        bool $includeChildLocations = false,
        array $domains = [],
        string $action = 'compile'
    ) {
        /** @var Library */
        $library = Library::findOrFail($libraryId);
        /** @var Location */
        $location = Location::findOrFail($locationId);

        $locationIds = $includeChildLocations
            ? $location->descendants()->get([(new Location())->getTable() . '.id'])->pluck('id')->toArray()
            : [];
        $locationIds[] = $location->id;

        $query = Reference::forLocations($locationIds)
            ->when(!empty($domains), function ($q) use ($domains) {
                $q->forDomains($domains);
            })
            ->typeCitation()
            ->compilable()
            ->hasActiveWork();

        $query->chunk(500, function ($references) use ($action, $library) {
            if ($action === 'decompile') {
                $this->libraryReferenceStore->detachReferences($library, $references);
            } else {
                $this->libraryReferenceStore->attachReferences($library, $references);
            }
        });
    }
}
