<?php

namespace App\Http\Resources\Internals\My\Corpus;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Corpus\ReferenceContent */
class ReferenceContentResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'content' => $this->cached_content,
        ];
    }
}
