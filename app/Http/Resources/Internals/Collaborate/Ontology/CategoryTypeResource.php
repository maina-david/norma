<?php

namespace App\Http\Resources\Internals\Collaborate\Ontology;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Ontology\CategoryType */
class CategoryTypeResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'colour' => $this->colour,
        ];
    }
}
