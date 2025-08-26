<?php

namespace App\Http\Resources\Notify\LegalUpdate\V2;

use App\Http\Resources\AbstractResource;
use App\Models\Notify\LegalUpdate;

/**
 * @mixin LegalUpdate
 */
class ForNormasLegalUpdateResource extends AbstractResource
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
        return [
            'id' => $this->id,
            'hash_id' => $this->hash_id,
            'title' => $this->title,
            'notification_date' => $this->notification_date->format('Y-m-d'),
            'normas' => $this->normas->pluck('id')->toArray(),
            'interpretation' => $this->work->highlights ?? $this->highlights ?? '',
            'effective_date' => $this->effective_date?->format('Y-m-d') ?? '', // not sure why we did empty string rather than null here, but keeping for backwards compatibility...
            'notice_number' => $this->work->notice_number ?? $this->publication_document_number ?? '', // not sure why we did empty string rather than null here...
            'work_type' => $this->work->work_type ?? null,
        ];
    }
}
