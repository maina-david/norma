<?php

namespace App\Http\Controllers\Ontology\My;

use App\Http\Controllers\Controller;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Tag;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexJson(Request $request): JsonResponse
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        if ($manager->isSingleMode()) {
            /** @var Norma */
            $norma = $manager->getActive();
            $query = Tag::forNorma($norma);
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            /** @var \App\Models\Auth\User $user */
            $user = $request->user();
            $query = Tag::forOrganisationUserAccess($organisation, $user);
        }

        $data = $query
            ->filter($request->only(['search']))
            ->get(['id', 'title'])
            ->toArray();

        return response()->json($data);
    }
}
