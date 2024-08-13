<?php

namespace App\Http\Controllers\Collaborators\Collaborate;

use App\Http\Controllers\Controller;
use App\Models\Collaborators\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupJsonController extends Controller
{
    /**
     * Get the listing of groups.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $json = Group::filter($request->all())
            ->get(['id', 'title'])
            ->map(function ($group) {
                /** @var Group $group */
                return ['id' => $group->id, 'title' => $group->title];
            })
            ->sortBy('title')
            ->toArray();

        return response()->json($json);
    }
}
