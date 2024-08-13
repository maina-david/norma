<?php

namespace App\Http\Resources\Corpus\Reference\V1;

use App\Http\Resources\V1ResourceCollection;

class ReferenceCollection extends V1ResourceCollection
{
    public $collects = ReferenceResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    // public function toArray($request)
    // {
    //     return ['data' => $this->collection];
    // }
}
