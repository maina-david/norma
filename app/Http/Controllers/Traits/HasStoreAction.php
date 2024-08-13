<?php

namespace App\Http\Controllers\Traits;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use ReflectionException;
use Throwable;

trait HasStoreAction
{
    /**
     * Create the specified resource in storage.
     *
     * @throws AuthorizationException
     * @throws ReflectionException
     * @throws Throwable
     *
     * @return RedirectResponse
     */
    public function store(): RedirectResponse
    {
        if (property_exists($this, 'withCreate')) {
            abort_unless($this->withCreate, 404);
        }
        if (method_exists($this, 'authoriseAction')) {
            $this->authoriseAction('create');
        }

        $request = app(static::resourceFormRequest());

        $resourceClass = static::resource();

        /** @var Model $resource */
        $resource = new $resourceClass();
        $resource->fill($request->validated());

        DB::transaction(function () use ($resource, $request) {
            $this->preStore($resource, $request);

            $resource->save();

            $this->postStore($resource, $request);
        });

        Session::flash('flash.message', __('notifications.successfully_created'));

        return redirect()->to($this->redirectAfterStore($resource));
    }

    /**
     * Used if the controller is for a pivot relation.
     *
     * @param int $parentResource
     *
     * @return RedirectResponse
     */
    public function storePivot(int $parentResource): RedirectResponse
    {
        if (property_exists($this, 'withCreate')) {
            abort_unless($this->withCreate, 404);
        }
        if (method_exists($this, 'authoriseAction')) {
            $this->authoriseAction('create');
        }
        if (
            !method_exists($this, 'forResource') || !method_exists($this, 'pivotClassName') || !method_exists($this, 'pivotRelationName')
        ) {
            // @codeCoverageIgnoreStart
            throw new Exception('Please implement all pivot methods before using storePivot');
            // @codeCoverageIgnoreEnd
        }

        $request = app(static::resourceFormRequest());
        $ids = $request->validated()['ids'];
        $pivotClassName = static::pivotClassName();
        // @phpstan-ignore-next-line
        $table = (new $pivotClassName())->getTable();
        $columns = Schema::getColumnListing($table);
        $pivots = Arr::only($request->validated(), [...$columns]);

        $forResource = static::forResource();
        if (!$forResource) {
            // @codeCoverageIgnoreStart
            throw new Exception('Please implement the forResource method');
            // @codeCoverageIgnoreEnd
        }
        /** @var Model */
        $forResource = $forResource::findOrFail($parentResource);

        $resource = static::resource();

        /** @var Collection<Model> */
        $toAttach = $resource::whereKey($ids)->get(['id']);

        DB::transaction(function () use ($forResource, $request, $toAttach, $pivots) {
            $this->preStore($forResource, $request);

            foreach ($toAttach as $child) {
                try {
                    $forResource->{static::pivotRelationName()}()->attach($child, $pivots);
                    // @codeCoverageIgnoreStart
                } catch (Throwable $th) {
                    if ($th->getCode() !== '23000') {
                        throw $th;
                    }
                }
                // @codeCoverageIgnoreEnd
            }

            $this->postStore($forResource, $request);
        });

        Session::flash('flash.message', __('notifications.successfully_created'));

        return redirect()->to($this->redirectAfterStore($forResource));
    }

    /**
     * Which route to redirect to after store action.
     *
     * @param Model $model
     *
     * @return string
     */
    protected function redirectAfterStore(Model $model): string
    {
        $route = static::resourceRoute();

        // if it's for a pivot table there's no show
        if (static::forResource()) {
            /* @phpstan-ignore-next-line */
            return route("{$route}.index", [$this->getForResource()->id, $model->id]);
        }

        /* @phpstan-ignore-next-line */
        return route("{$route}.show", $model->id);
    }

    /**
     * Pre-Store hook.
     *
     * @param Model   $model
     * @param Request $request
     *
     * @return void
     */
    protected function preStore(Model $model, Request $request): void
    {
    }

    /**
     * Post-Store hook.
     *
     * @param Model   $model
     * @param Request $request
     *
     * @return void
     */
    protected function postStore(Model $model, Request $request): void
    {
    }
}
