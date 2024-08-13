<?php

namespace App\Http\Controllers\Api\Internals\My\Corpus;

use App\Http\Controllers\Controller;
use App\Http\Resources\Internals\My\Actions\ActionAreaResource;
use App\Models\Corpus\Reference;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReferenceLinkedActionAreasController extends Controller
{
    /**
     * Returns Linked action areas in advanced applicability section.
     *
     * @param Reference $reference
     *
     * @return AnonymousResourceCollection
     */
    public function index(Reference $reference): AnonymousResourceCollection
    {
        return ActionAreaResource::collection($reference->actionAreas);
    }
}
