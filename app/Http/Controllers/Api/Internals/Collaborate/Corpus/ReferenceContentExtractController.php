<?php

namespace App\Http\Controllers\Api\Internals\Collaborate\Corpus;

use App\Http\Resources\Internals\Collaborate\Corpus\ReferenceContentExtractResource;
use App\Models\Corpus\ReferenceContentExtract;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReferenceContentExtractController
{
    /**
     * Get the cached content.
     *
     * @param int $reference
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function show(int $reference): AnonymousResourceCollection
    {
        $extracts = ReferenceContentExtract::where('reference_id', $reference)->get(['id', 'content']);

        return ReferenceContentExtractResource::collection($extracts);
    }
}
