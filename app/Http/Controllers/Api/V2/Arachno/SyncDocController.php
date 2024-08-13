<?php

namespace App\Http\Controllers\Api\V2\Arachno;

use App\Actions\Arachno\Legacy\SyncDoc;
use App\Http\Controllers\Controller;
use App\Models\Corpus\WorkExpression;
use Illuminate\Http\JsonResponse;

class SyncDocController extends Controller
{
    public function __invoke(WorkExpression $workExpression): JsonResponse
    {
        SyncDoc::dispatch($workExpression);

        return response()->json();
    }
}
