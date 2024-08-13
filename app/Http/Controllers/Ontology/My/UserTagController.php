<?php

namespace App\Http\Controllers\Ontology\My;

use App\Http\Controllers\Controller;
use App\Models\Ontology\UserTag;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserTagController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexJson(Request $request): JsonResponse
    {
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $organisation = $manager->getActiveOrganisation();

        $json = UserTag::forOrganisation($organisation->id)
            ->filter($request->all())
            ->get(['id', 'title'])
            ->toArray();

        return response()->json($json);
    }
}
