<?php

namespace App\Http\Resources\Corpus\Reference\V2;

use App\Http\Resources\AbstractResource;
use App\Http\Resources\Corpus\Work\V2\WorkResource;
use App\Models\Corpus\Reference;

/**
 * @mixin Reference
 */
class BasicReferenceResource extends AbstractResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string,mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'citation_type' => null, // legacy
            'number' => $this->refPlainText?->plain_text ? null : ($this->citation->number ?? ''),
            'register_id' => $this->work_id,
            'title' => $this->refPlainText?->plain_text ?? ($this->citation->heading ?? null),
            'sub_line' => null, // legacy
            'derived' => false,
            'position' => $this->start,
            'work' => new WorkResource($this->whenLoaded('work')),
            'is_toc_item' => true,
            'is_section' => $this->is_section ?? ($this->citation->is_section ?? false),
            'level' => $this->level,
            'volume' => $this->volume,
            'type' => $this->type,
            'content' => $this->htmlContent?->cached_content,
        ];
    }
}
