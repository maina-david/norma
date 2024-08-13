<?php

namespace App\Http\Resources\Corpus\Reference\V1;

use App\Http\Resources\V1ResourceCollection;

class RaisesConsequenceGroupCollection extends V1ResourceCollection
{
    public $collects = RaisesConsequenceGroupResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return ['data' => $this->collection];
    }
}
