<?php

namespace App\Http\Resources\Internals\Collaborate\Corpus;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Corpus\TocItem */
class TocItemResource extends JsonResource
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
            'id' => $this->whenHas('id'),
            'source_unique_id' => $this->whenHas('source_unique_id'),
            'crawl_id' => $this->whenHas('crawl_id'),
            'uri_fragment' => $this->whenHas('uri_fragment'),
            'label' => $this->whenHas('label'),
            'level' => $this->whenHas('level'),
            'position' => $this->whenHas('position'),
            'created_at' => $this->whenHas('created_at'),
            'updated_at' => $this->whenHas('updated_at'),
            'source_url' => $this->whenHas('source_url'),
            'doc_position' => $this->whenHas('doc_position'),
            'content_hash' => $this->whenHas('content_hash'),
            'uid_hash' => $this->whenHas('uid_hash'),
            'uid' => $this->whenHas('uid'),
            'doc_id' => $this->whenHas('doc_id'),
            'link_id' => $this->whenHas('link_id'),
            'parent_id' => $this->whenHas('parent_id'),
            'content_resource_id' => $this->whenHas('content_resource_id'),
            'children_count' => $this->children_count,
            'has_reference' => $this->whenHas('has_reference'),
            'requirement_score' => $this->whenHas('requirement_score', fn () => $this->requirement_score ?? 0),
        ];
    }
}
