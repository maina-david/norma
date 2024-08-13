<?php

namespace App\Http\Controllers\Collaborators\Collaborate;

use App\Http\Controllers\Controller;
use App\Models\Collaborators\Collaborator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollaboratorJsonController extends Controller
{
    /**
     * Get the listing of collaborators.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $json = Collaborator::filter($request->all())
            ->active()
            ->get(['id', 'fname', 'sname'])
            ->map(function ($collaborator) {
                /** @var Collaborator $collaborator */
                return ['id' => $collaborator->id, 'title' => $collaborator->full_name];
            })
            ->sortBy('title')
            ->toArray();

        $json[] = ['id' => 'none', 'title' => __('tasks.unassigned')];

        return response()->json($json);
    }
}
