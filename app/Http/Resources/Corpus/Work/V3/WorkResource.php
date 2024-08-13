<?php

namespace App\Http\Resources\Corpus\Work\V3;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Corpus\Work */
class WorkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'work_type' => $this->work_type,
            'language_code' => $this->language_code,
            'publication_date' => $this->work_date,
            'effective_date' => $this->effective_date,
            'link' => route('my.corpus.works.show', ['work' => $this->id], false),
            'children' => $this->children->pluck('id')->all(),
        ];
    }
}
