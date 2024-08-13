<?php

namespace App\Http\Resources\Corpus\Reference\V1;

use App\Http\Resources\AbstractResource;
use App\Http\Resources\Requirements\Consequence\V1\ConsequenceCollection;
use App\Models\Corpus\Reference;

/**
 * @mixin Reference
 */
class RaisesConsequenceGroupResource extends AbstractResource
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
        /** @var array<string, mixed> */
        return [
            'id' => (int) $this->id,
            'consequences' => new ConsequenceCollection($this->whenLoaded('consequences')),
        ];
    }
}
