<?php

namespace App\Observers\Actions;

use App\Models\Actions\ActionArea;

class ActionAreaObserver
{
    /**
     * Listen to the saving event.
     *
     * @param \App\Models\Actions\ActionArea $area
     *
     * @return void
     */
    public function saving(ActionArea $area): void
    {
        $area->load(['subjectCategory', 'controlCategory']);

        $area->title = strtolower(sprintf('%s relating to %s', $area->controlCategory->label, $area->subjectCategory->label));
        $area->title = ucfirst($area->title);
    }
}
