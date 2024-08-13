<?php

namespace App\Http\Resources\Requirements\Summary\V1;

use App\Http\Resources\AbstractResource;
use App\Models\Requirements\Summary;

/**
 * @mixin Summary
 */
class SummaryResource extends AbstractResource
{
    /**
     * Copied from the original transformer in the old code base - so lots of legacy here.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return array_merge([
            'id' => (int) $this->reference_id,
            'summary_body' => $this->summary_body,
        ], $this->timestamps());
    }
}
