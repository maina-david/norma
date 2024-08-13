<?php

namespace App\Http\Resources\Internals\My\Corpus;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Corpus\ReferenceContentExtract */
class ReferenceContentExtractResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'attached' => $this->attached, // @phpstan-ignore-line
        ];
    }
}
