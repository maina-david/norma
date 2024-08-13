<?php

namespace App\Http\Resources\Internals\Collaborate\Ontology;

use App\Models\Ontology\Pivots\CategoryReferenceDraft;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CategoryReferenceDraft $pivot
 *
 * @mixin \App\Models\Ontology\Category
 */
class CategoryResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->display_label,
            'change_status' => $this->whenPivotLoaded(CategoryReferenceDraft::class, fn () => $this->pivot->change_status),
            'category_type' => new CategoryTypeResource($this->whenLoaded('categoryType')),
        ];
    }
}
