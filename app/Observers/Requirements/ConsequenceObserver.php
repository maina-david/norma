<?php

namespace App\Observers\Requirements;

use App\Models\Requirements\Consequence;
use App\Stores\Requirements\ConsequenceStore;

class ConsequenceObserver
{
    /**
     * @param ConsequenceStore $store
     */
    public function __construct(protected ConsequenceStore $store)
    {
    }

    /**
     * Listen to the saving event.
     *
     * @param Consequence $consequence
     */
    public function saving(Consequence $consequence): void
    {
        $this->store->preSave($consequence);
        $this->store->validateDuplicate($consequence);
    }
}
