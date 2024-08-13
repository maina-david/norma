<?php

namespace App\Http\Controllers\Traits;

use App\Support\ResourceNames;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

trait HasEditAction
{
    /**
     * Show the form for editing the specified resource.
     *
     * @param int     $resourceId
     * @param Request $request
     *
     * @throws AuthorizationException
     *
     * @return View
     */
    public function edit(Request $request, int $resourceId): View
    {
        if (property_exists($this, 'withUpdate')) {
            abort_unless($this->withUpdate, 404);
        }

        $resourceId = method_exists($this, 'getResourceId') ? $this->getResourceId() : $resourceId;

        $resource = static::resource();
        $resource = method_exists($this, 'baseQuery')
            ? $this->baseQuery($request)->findOrFail($resourceId)
            // @codeCoverageIgnoreStart
            : $resource::findOrFail($resourceId);
        // @codeCoverageIgnoreEnd

        if (method_exists($this, 'authoriseAction')) {
            $this->authoriseAction('edit', $resource);
        }

        return $this->getView('edit')
            ->with(array_merge(
                [
                    'permission' => method_exists($this, 'permissionPrefix') ? $this->permissionPrefix() : '',
                    'resource' => $resource,
                    'form' => $this->getFormView(),
                    'langFile' => ResourceNames::getLangPath(static::resource()),
                    'baseRoute' => static::resourceRoute(),
                    'baseRouteParams' => method_exists($this, 'resourceRouteParams')
                        ? $this->resourceRouteParams()
                        : [],
                    'application' => static::application(),
                    'appLayout' => $this->appLayout(),
                    'forResource' => method_exists($this, 'forResource') && static::forResource() ? $this->getForResource() : null,
                ],
                $this->editViewData($request),
            ));
    }

    /**
     * Get extra data to be sent back to the edit view.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function editViewData(Request $request): array
    {
        return [];
    }
}
