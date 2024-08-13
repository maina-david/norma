<?php

namespace App\Stores\Compilation;

use App\Models\Compilation\Library;
use App\Models\Corpus\Reference;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class LibraryReferenceStore
{
    use AttachesDetaches;

    /**
     * @param Library                   $library
     * @param Collection<Reference|int> $references
     *
     * @return void
     */
    public function attachReferences(Library $library, Collection $references): void
    {
        $this->attachRelations($library, 'references', $references);
        $this->markForRecompilation($library);
    }

    /**
     * @param Library                   $library
     * @param Collection<Reference|int> $references
     *
     * @return void
     */
    public function detachReferences(Library $library, Collection $references): void
    {
        $this->detachRelations($library, 'references', $references);
        $this->markForRecompilation($library);
    }

    /**
     * Attach if don't exist, and detach references that don't exist.
     *
     * @param Library    $library
     * @param array<int> $referenceIds
     *
     * @return void
     */
    public function syncReferences(Library $library, array $referenceIds): void
    {
        try {
            $library->references()->sync($referenceIds);
            // @codeCoverageIgnoreStart
        } catch (Throwable $th) {
        }
        // @codeCoverageIgnoreEnd
    }

    private function markForRecompilation(Library $library): void
    {
        $library->applicableLibryos()->each(fn ($libryo) => $libryo->markAsNeedingRecompilation());
    }
}
