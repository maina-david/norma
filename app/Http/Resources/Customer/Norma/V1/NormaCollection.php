<?php

namespace App\Http\Resources\Customer\Norma\V1;

use App\Http\Resources\V1ResourceCollection;

class NormaCollection extends V1ResourceCollection
{
    public $collects = NormaResource::class;
}
