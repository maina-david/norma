<?php

namespace App\Http\Controllers\Traits;

use App\Support\ResourceNames;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

trait HasShowAction
{
    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @param int     $resourceId
     *
     * @throws AuthorizationException
     *
     * @return View|Response
     */
    public function show(Request $request, int $resourceId): View|Response
    {
        if (property_exists($this, 'withShow')) {
            abort_unless($this->withShow, 404);
        }

        $resourceId = method_exists($this, 'getResourceId') ? $this->getResourceId() : $resourceId;

        $resource = method_exists($this, 'showBaseQuery')
            ? $this->showBaseQuery($request)->findOrFail($resourceId)
            : $this->baseQuery($request)->findOrFail($resourceId);

        if (method_exists($this, 'authoriseAction')) {
            $this->authoriseAction('show', $resource);
        }

        return $this->getView('show')
            ->with(array_merge(
                [
                    'resource' => $resource,
                    'view' => $this->getShowView(),
                    'langFile' => ResourceNames::getLangPath(static::resource()),
                    'baseRoute' => static::resourceRoute(),
                    'baseRouteParams' => method_exists($this, 'resourceRouteParams')
                        ? $this->resourceRouteParams()
                        : [],
                    'application' => static::application(),
                    'appLayout' => $this->appLayout(),
                    'permission' => method_exists($this, 'permissionPrefix') ? $this->permissionPrefix() : '',
                ],
                $this->showViewData($request),
            ));
    }

    /**
     * Get extra data to be sent back to the show view.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function showViewData(Request $request): array
    {
        return [];
    }
}
