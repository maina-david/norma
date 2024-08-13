<?php

namespace App\Http\Controllers\Geonames\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Models\Geonames\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocationController extends CollaborateController
{
    /** @var bool */
    protected bool $withCreate = false;

    /** @var bool */
    protected bool $withShow = false;

    /** @var bool */
    protected bool $withDelete = false;

    /** @var bool */
    protected bool $withUpdate = false;

    /** @var string */
    protected string $sortBy = 'title';

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Location::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.jurisdictions';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return '';
    }

    /**
     * Get the model columns that should be displayed in the index table.
     *
     * @return array<string, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'title' => fn ($row) => static::renderPartial($row, 'title-column'),
            'country' => fn ($row) => static::renderPartial($row, 'country-column'),
            'level' => fn ($row) => static::renderPartial($row, 'level-column'),
            'show_title' => fn ($row) => static::renderLink(route('collaborate.jurisdictions.show', $row->id), __('interface.view')),
        ];
    }

    /**
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        /** @var \Illuminate\Routing\Route $route */
        $route = $request->route();
        /** @var string|null $parent */
        $parent = $route->parameter('jurisdiction');
        $isSearch = $request->has('search');

        /** @var Builder<Location> $query */
        $query = parent::baseQuery($request);

        return $query
            ->when($parent, fn ($q) => $q->forParent((int) $parent))
            ->when(is_null($parent) && !$isSearch, fn ($q) => $q->noParent())
            ->with(['type', 'country'])
            ->withCount('children');
    }

    /**
     * {@inheritDoc}
     */
    protected function showBaseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)
            ->with(['type', 'country', 'parent'])
            ->withCount('children');
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        /** @var \Illuminate\Routing\Route $route */
        $route = $request->route();
        /** @var string|null $parentId */
        $parentId = $route->parameter('jurisdiction');
        /** @var Location|null $parent */
        $parent = $parentId ? Location::with('ancestorsWithSelf')->find($parentId) : null;

        return [
            'forResourceTitle' => __('nav.jurisdictions'),
            'forResourceRoute' => route('collaborate.jurisdictions.index'),
            'title' => $parent?->ancestorsWithSelf->implode('title', ' > '),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function show(Request $request, int $resourceId): View|Response
    {
        $this->withShow = true;

        return parent::show($request, $resourceId);
    }
}
