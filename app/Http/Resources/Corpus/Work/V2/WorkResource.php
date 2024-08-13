<?php

namespace App\Http\Resources\Corpus\Work\V2;

use App\Http\Resources\AbstractResource;
use App\Models\Corpus\Work;

/**
 * @mixin Work
 */
class WorkResource extends AbstractResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'iri' => $this->iri,
            'work_number' => $this->work_number,
            'title' => $this->title,
            'status' => (int) $this->status,
            'has_xml' => false, // legacy
            'work_type' => $this->getTypeName(),
            'sub_type' => $this->sub_type,
            'language_code' => $this->language_code,
            'enacted_date' => null, // legacy
            'publication_date' => null, // legacy
            'primary_location_id' => $this->primary_location_id,
            'title_translation' => $this->title_translation,
            'short_title' => $this->short_title,
            'highlights' => $this->highlights,
            'update_report' => $this->update_report,
            'issuing_authority' => $this->issuing_authority,
            'effective_date' => $this->effective_date?->format('Y-m-d') ?? null,
            'comment_date' => $this->comment_date?->format('Y-m-d') ?? null,
            'gazette_number' => $this->gazette_number,
            'notice_number' => $this->notice_number,
        ];
    }
}
