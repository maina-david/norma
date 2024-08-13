<?php

namespace App\Http\Resources\Ontology\LegalDomain\V1;

use App\Models\Ontology\LegalDomain;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LegalDomain
 */
class LegalDomainResource extends JsonResource
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
            'title' => $this->title,
        ];
    }
}
