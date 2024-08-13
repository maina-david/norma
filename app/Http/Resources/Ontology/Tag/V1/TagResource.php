<?php

namespace App\Http\Resources\Ontology\Tag\V1;

use App\Http\Resources\AbstractResource;
use App\Models\Ontology\Tag;

/**
 * @mixin Tag
 */
class TagResource extends AbstractResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return array_merge([
            'id' => $this->id,
            'title' => $this->title,
            'tag_type_id' => $this->tag_type_id,
        ], $this->timestamps());
    }
}
