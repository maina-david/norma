<?php

namespace App\Http\Resources\Notify\LegalUpdate\V3;

use App\Services\Html\HtmlToText;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Notify\LegalUpdate */
class LegalUpdateResource extends JsonResource
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
            'publication_number' => $this->publication_number,
            'publication_document_number' => $this->publication_document_number,
            'publication_date' => $this->publication_date?->toDateString(),
            'effective_date' => $this->effective_date?->toDateString(),
            'highlights' => trim(app(HtmlToText::class)->convert($this->highlights ?? '')),
            'streams' => $this->normas->pluck('id')->all(),
            'link' => route('my.notify.legal-updates.show', ['update' => $this->id], false),
        ];
    }
}
