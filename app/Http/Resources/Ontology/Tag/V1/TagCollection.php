<?php

namespace App\Http\Resources\Ontology\Tag\V1;

use App\Http\Resources\V1ResourceCollection;

class TagCollection extends V1ResourceCollection
{
    public $collects = TagResource::class;

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
