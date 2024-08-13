<?php

namespace App\Stores\Corpus;

use App\Models\Corpus\Work;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Support\Collection;

class WorkStore
{
    use AttachesDetaches;

    /**
     * Attach child works to the selected work.
     *
     * @param Work       $work
     * @param Collection $children
     *
     * @return void
     */
    public function attachChildren(Work $work, Collection $children): void
    {
        $this->attachRelations($work, 'children', $children);
    }

    /**
     * Attach parent works to the selected work.
     *
     * @param Work       $work
     * @param Collection $children
     *
     * @return void
     */
    public function attachParents(Work $work, Collection $children): void
    {
        $this->attachRelations($work, 'parents', $children);
    }
}
