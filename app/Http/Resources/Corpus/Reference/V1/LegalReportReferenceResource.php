<?php

namespace App\Http\Resources\Corpus\Reference\V1;

use App\Http\Resources\AbstractResource;
use App\Models\Corpus\Reference;

/**
 * @mixin Reference
 */
class LegalReportReferenceResource extends AbstractResource
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
            'number' => $this->refPlainText?->plain_text ? '' : ($this->citation->number ?? ''),
            'register_id' => $this->work_id,
            'title' => $this->refPlainText?->plain_text ?? ($this->citation->heading ?? ''),
            'position' => $this->position ?? ($this->citation->position ?? ''),
        ], $this->timestamps());
    }
}
