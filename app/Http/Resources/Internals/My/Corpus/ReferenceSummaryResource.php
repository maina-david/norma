<?php

namespace App\Http\Resources\Internals\My\Corpus;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Requirements\Summary */
class ReferenceSummaryResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'content' => $this->summary_body,
        ];
    }
}
