<?php

namespace App\Http\Resources\Requirements\Consequence\V1;

use App\Http\Resources\V1ResourceCollection;

class ConsequenceCollection extends V1ResourceCollection
{
    public $collects = ConsequenceResource::class;

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
