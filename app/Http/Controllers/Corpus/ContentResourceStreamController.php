<?php

namespace App\Http\Controllers\Corpus;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\UsesContentResource;
use App\Models\Corpus\ContentResource;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentResourceStreamController extends Controller
{
    use UsesContentResource;

    public function show(Request $request, ContentResource $resource, int $targetId): Response
    {
        return $this->getContentResource($resource, $targetId)->toResponse($request);
    }
}
