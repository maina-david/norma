<?php

namespace App\View\Components\Corpus\Work\My;

use App\Models\Corpus\Work;
use App\Models\Geonames\Location;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class WorkFlags extends Component
{
    /**
     * @param Work $work
     * @param bool $showMultipleFlags
     * @param int  $size
     *
     * @return void
     */
    public function __construct(public Work $work, public bool $showMultipleFlags = false, public int $size = 8)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        /** @var View */
        return view('components.corpus.work.my.work-flags');
    }

    /**
     * @codeCoverageIgnore
     *
     * @return Location|null
     */
    public function getLocation(): ?Location
    {
        return $this->work->primaryLocation;
    }

    /**
     * @return Collection<Location>
     */
    public function getLocations(): Collection
    {
        /** @var Collection $locations */
        $locations = Location::whereRelation('references', 'work_id', $this->work->id)->get();

        return $locations->unique('flag');
    }
}
