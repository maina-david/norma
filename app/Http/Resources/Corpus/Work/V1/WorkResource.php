<?php

namespace App\Http\Resources\Corpus\Work\V1;

use App\Http\Resources\AbstractResource;
use App\Http\Resources\Corpus\Reference\V1\NestedReferenceCollection;
use App\Models\Corpus\Work;
use Illuminate\Http\Request;

/**
 * @mixin Work
 */
class WorkResource extends AbstractResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return array_merge([
            'id' => (int) $this->id,
            'title' => $this->title,
            'title_translation' => $this->title_translation,
            'status' => $this->status,
            'register_type' => $this->getTypeName(),
            'source_text' => $this->source_url ?? '',
            'url' => $this->source_url ?? '',
            'source_id' => $this->source_id,
            'language_code' => $this->language_code,
            'work_number' => $this->work_number,
            'iri' => $this->iri,
            'sub_type' => $this->sub_type,
            'creation_date' => $this->work_date,
            'publication_date' => $this->effective_date?->format('Y-m-d') ?? null,
            'registerItems' => new NestedReferenceCollection($this->whenLoaded('registerItems')),
        ], $this->timestamps());
    }
}
