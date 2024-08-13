<?php

namespace App\Http\Resources\Internals\Collaborate\Ontology;

use App\Models\Corpus\Pivots\LegalDomainReferenceDraft;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Ontology\LegalDomain
 *
 * @property LegalDomainReferenceDraft $pivot
 */
class LegalDomainResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'change_status' => $this->whenPivotLoaded(LegalDomainReferenceDraft::class, fn () => $this->pivot->change_status),
        ];
    }
}
