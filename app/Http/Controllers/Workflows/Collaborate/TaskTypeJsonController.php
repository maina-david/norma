<?php

namespace App\Http\Controllers\Workflows\Collaborate;

use App\Http\Controllers\Controller;
use App\Models\Workflows\TaskType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskTypeJsonController extends Controller
{
    /**
     * Get the listing of the children.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $json = TaskType::filter($request->all())
            ->orderBy('name')
            ->get()
            ->map(fn (TaskType $type) => ['id' => $type->id, 'title' => $type->name])
            ->toArray();

        return response()->json($json);
    }
}
