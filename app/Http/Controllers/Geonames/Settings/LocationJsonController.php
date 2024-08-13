<?php

namespace App\Http\Controllers\Geonames\Settings;

use App\Http\Controllers\Controller;
use App\Models\Geonames\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationJsonController extends Controller
{
    /**
     * Search the locations.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->get('search');

        // @codeCoverageIgnoreStart
        if ($search && strlen($search) < 3) {
            return response()->json([]);
        }
        // @codeCoverageIgnoreEnd

        $json = Location::with(['ancestors', 'type', 'ancestors.type'])
            ->filter($request->all())
            ->get(['id', 'title'])
            ->map(function ($l) {
                $ancestorStr = $l->getBreadcrumbs();

                return ['id' => $l->id, 'title' => $l->title, 'details' => $ancestorStr ? $ancestorStr : ''];
            })
            ->toArray();

        return response()->json($json);
    }
}
