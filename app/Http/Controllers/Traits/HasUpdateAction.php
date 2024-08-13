<?php

namespace App\Http\Controllers\Traits;

use App\Support\ResourceNames;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use ReflectionException;

trait HasUpdateAction
{
    /**
     * Update the specified resource in storage.
     *
     * @param int $resourceId
     *
     * @throws AuthorizationException
     * @throws ReflectionException
     *
     * @return RedirectResponse
     */
    public function update(int $resourceId): RedirectResponse
    {
        if (property_exists($this, 'withUpdate')) {
            abort_unless($this->withUpdate, 404);
        }

        /** @var Request $request */
        $request = request();
        $resource = static::resource();

        $resourceId = method_exists($this, 'getResourceId') ? $this->getResourceId() : $resourceId;

        $resource = method_exists($this, 'baseQuery')
            ? $this->baseQuery($request)->findOrFail($resourceId)
            // @codeCoverageIgnoreStart
            : $resource::findOrFail($resourceId);
        // @codeCoverageIgnoreEnd

        if (method_exists($this, 'authoriseAction')) {
            $this->authoriseAction('update', $resource);
        }

        $request = app(static::resourceFormRequest());

        $resource = $resource->fill($request->validated());

        DB::transaction(function () use ($resource, $request) {
            $this->preUpdate($resource, $request);

            $resource->save();

            $this->postUpdate($resource, $request);
        });

        Session::flash('flash.message', __('notifications.successfully_updated'));

        return redirect()->to($this->redirectAfterUpdate($resource));
    }

    /**
     * Which route to redirect to after update action.
     *
     * @param Model $model
     *
     * @throws ReflectionException
     *
     * @return string
     */
    protected function redirectAfterUpdate(Model $model): string
    {
        $route = static::resourceRoute();
        // if it's for a pivot table there's no show
        if (static::forResource()) {
            /* @phpstan-ignore-next-line */
            return route("{$route}.index", [$this->getForResource()->id, $model->id]);
        }

        /* @phpstan-ignore-next-line */
        return route("{$route}.show", [Str::snake(ResourceNames::getName(static::resource())) => $model->id]);
    }

    /**
     * Pre-Update hook.
     *
     * @param Model   $model
     * @param Request $request
     *
     * @return void
     */
    protected function preUpdate(Model $model, Request $request): void
    {
    }

    /**
     * Post-Update hook.
     *
     * @param Model   $model
     * @param Request $request
     *
     * @return void
     */
    protected function postUpdate(Model $model, Request $request): void
    {
    }
}
