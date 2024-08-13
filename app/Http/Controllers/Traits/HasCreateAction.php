<?php

namespace App\Http\Controllers\Traits;

use App\Support\ResourceNames;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use ReflectionException;
use Symfony\Component\HttpFoundation\Response;

trait HasCreateAction
{
    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     *
     * @throws ReflectionException
     *
     * @return View|Response
     */
    public function create(Request $request): View|Response
    {
        if (property_exists($this, 'withCreate')) {
            abort_unless($this->withCreate, 404);
        }

        if (method_exists($this, 'authoriseAction')) {
            $this->authoriseAction('create');
        }

        return $this->getView('create')
            ->with(array_merge(
                [
                    'permission' => method_exists($this, 'permissionPrefix') ? $this->permissionPrefix() : '',
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
                $this->createViewData($request),
            ));
    }

    /**
     * Get extra data to be sent back to the create view.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function createViewData(Request $request): array
    {
        return [];
    }
}
