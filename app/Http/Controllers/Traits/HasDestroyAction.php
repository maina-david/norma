<?php

namespace App\Http\Controllers\Traits;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use ReflectionException;
use Throwable;

trait HasDestroyAction
{
    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $resourceId
     *
     * @throws AuthorizationException
     * @throws ReflectionException
     * @throws Throwable
     *
     * @return RedirectResponse
     */
    public function destroy(Request $request, int $resourceId): RedirectResponse
    {
        if (property_exists($this, 'withDelete')) {
            abort_unless($this->withDelete, 404);
        }
        $resource = static::resource();

        $resourceId = method_exists($this, 'getResourceId') ? $this->getResourceId() : $resourceId;

        $resource = method_exists($this, 'baseQuery')
            ? $this->baseQuery($request)->findOrFail($resourceId)
            // @codeCoverageIgnoreStart
            : $resource::findOrFail($resourceId);
        // @codeCoverageIgnoreEnd

        if (method_exists($this, 'authoriseAction')) {
            $this->authoriseAction('delete', $resource);
        }

        DB::transaction(function () use ($request, $resource) {
            $this->preDestroy($resource, $request);

            $resource->delete();

            $this->postDestroy($resource, $request);
        });

        Session::flash('flash.message', __('notifications.successfully_deleted'));

        return redirect()->to($this->redirectAfterDestroy($resource));
    }

    /**
     * Used if the controller is for a pivot relation.
     *
     * @param int $parentResource
     * @param int $childResource
     *
     * @return RedirectResponse
     */
    public function destroyPivot(int $parentResource, int $childResource): RedirectResponse
    {
        if (property_exists($this, 'withDelete')) {
            abort_unless($this->withDelete, 404);
        }

        $prefix = $this->permissionPrefix();
        $this->authorize("{$prefix}.delete");

        $forResource = static::forResource();
        if (!$forResource) {
            // @codeCoverageIgnoreStart
            throw new Exception('Please implement the forResource method');
            // @codeCoverageIgnoreEnd
        }
        /** @var Model */
        $forResource = $forResource::findOrFail($parentResource);
        $resource = static::resource();
        /** @var Model */
        $pivot = $resource::findOrFail($childResource);

        $forResource->{static::pivotRelationName()}()->detach($pivot);

        Session::flash('flash.message', __('notifications.successfully_deleted'));

        return redirect()->to($this->redirectAfterDestroy($forResource));
    }

    /**
     * Which route to redirect to after destroy action.
     *
     * @param Model $model
     *
     * @return string
     */
    protected function redirectAfterDestroy(Model $model): string
    {
        $route = static::resourceRoute();

        return route("{$route}.index", $this->resourceRouteParams());
    }

    /**
     * Pre-Destroy hook.
     *
     * @param Model   $model
     * @param Request $request
     *
     * @return void
     */
    protected function preDestroy(Model $model, Request $request): void
    {
    }

    /**
     * Post-Destroy hook.
     *
     * @param Model   $model
     * @param Request $request
     *
     * @return void
     */
    protected function postDestroy(Model $model, Request $request): void
    {
    }
}
