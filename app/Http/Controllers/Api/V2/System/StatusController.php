<?php

namespace App\Http\Controllers\Api\V2\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * @codeCoverageIgnore
 */
class StatusController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(['Status' => 'Ok']);
    }
}
