<?php

namespace App\Http\Resources\Internals\Collaborate\Ontology;

use App\Models\Corpus\Pivots\ReferenceTagDraft;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ReferenceTagDraft $pivot
 *
 * @mixin \App\Models\Ontology\Tag
 */
class TagResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'change_status' => $this->whenPivotLoaded(ReferenceTagDraft::class, fn () => $this->pivot->change_status),
        ];
    }
}
