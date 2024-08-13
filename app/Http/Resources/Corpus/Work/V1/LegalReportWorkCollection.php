<?php

namespace App\Http\Resources\Corpus\Work\V1;

use App\Http\Resources\V1ResourceCollection;

class LegalReportWorkCollection extends V1ResourceCollection
{
    public $collects = LegalReportWorkResource::class;
}
