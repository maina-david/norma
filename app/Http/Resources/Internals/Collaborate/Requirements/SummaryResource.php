<?php

namespace App\Http\Resources\Internals\Collaborate\Requirements;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Requirements\Summary
 */
class SummaryResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->reference_id,
            'summary_body' => $this->summary_body,
        ];
    }
}
