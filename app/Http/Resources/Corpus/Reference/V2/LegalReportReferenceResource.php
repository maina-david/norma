<?php

namespace App\Http\Resources\Corpus\Reference\V2;

use App\Http\Resources\AbstractResource;
use App\Models\Corpus\Reference;

/**
 * @mixin Reference
 */
class LegalReportReferenceResource extends AbstractResource
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
            'work_id' => $this->work_id,
            'number' => $this->refPlainText?->plain_text ? '' : ($this->citation->number ?? ''),
            'title' => $this->refPlainText?->plain_text ?? ($this->citation->heading ?? ''),
        ];
    }
}
