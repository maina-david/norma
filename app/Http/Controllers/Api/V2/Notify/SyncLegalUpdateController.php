<?php

namespace App\Http\Controllers\Api\V2\Notify;

use App\Actions\Notify\LegalUpdate\SyncFromWork;
use Illuminate\Http\JsonResponse;

class SyncLegalUpdateController
{
    public function __invoke(int $update): JsonResponse
    {
        SyncFromWork::dispatch($update);

        return response()->json();
    }
}
