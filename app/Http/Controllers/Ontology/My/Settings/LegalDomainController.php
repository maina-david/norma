<?php

namespace App\Http\Controllers\Ontology\My\Settings;

use App\Http\Controllers\Controller;
use App\Models\Ontology\LegalDomain;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LegalDomainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        abort_unless($request->wantsJson(), 404);
        /** @var int|null $locationId */
        $locationId = $request->get('location_id');

        $json = LegalDomain::active()
            ->whereNull('parent_id')
            ->forLocation($locationId)
            ->newQuery()
            ->filter($request->all())
            ->get(['id', 'title'])
            ->map(function ($item) {
                /** @var LegalDomain $item */
                return ['id' => $item->id, 'title' => $item->title];
            })
            ->toArray();

        return response()->json($json);
    }
}
