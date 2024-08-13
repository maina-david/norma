<?php

namespace App\Http\Resources\Internals\Collaborate\Corpus;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Corpus\CatalogueDoc */
class CatalogueDocResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'view_url' => $this->view_url,
            'summary' => $this->summary,
            'created_at' => $this->created_at,
            'title' => $this->title,
            'id' => $this->id,
            'fetch_started_at' => $this->fetch_started_at,
            'language_code' => $this->language_code,
            'updated_at' => $this->updated_at,
            'title_translation' => $this->title_translation,
            'start_url' => $this->start_url,
            'post_data' => $this->post_data,
            'last_updated_at' => $this->last_updated_at,
            'latest_doc_created_at' => $this->latestDoc?->created_at?->format('d M Y') ?? null,

            'work' => new WorkResource($this->whenLoaded('work')),
        ];
    }
}
