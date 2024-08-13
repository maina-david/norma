<?php

namespace App\Http\Resources\Customer\Libryo\V1;

use App\Http\Resources\V1ResourceCollection;

class LibryoCollection extends V1ResourceCollection
{
    public $collects = LibryoResource::class;
}
