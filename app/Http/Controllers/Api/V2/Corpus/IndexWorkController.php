<?php

namespace App\Http\Controllers\Api\V2\Corpus;

use App\Http\Controllers\Controller;
use App\Jobs\Search\Elastic\SearchIndexWork;
use App\Models\Corpus\Work;
use Illuminate\Http\JsonResponse;

class IndexWorkController extends Controller
{
    public function __invoke(Work $work): JsonResponse
    {
        SearchIndexWork::dispatch($work->id);

        return response()->json();
    }
}
