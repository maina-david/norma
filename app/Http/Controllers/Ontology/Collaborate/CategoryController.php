<?php

namespace App\Http\Controllers\Ontology\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Ontology\CategoryRequest;
use App\Models\Ontology\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class CategoryController extends CollaborateController
{
    /** @var bool */
    protected bool $withCreate = true;

    /** @var bool */
    protected bool $withShow = false;

    /** @var bool */
    protected bool $searchable = true;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Category::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.ontology.categories';
    }

    /**
     * @codeCoverageIgnore
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return CategoryRequest::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(Request $request): Builder
    {
        /** @var Route $route */
        $route = $request->route();
        /** @var string|null */
        $parent = $route->parameter('parent');

        $isSearch = $request->has('search');

        /** @var Builder<Category> $query */
        $query = parent::baseQuery($request);

        return $query
            ->when($parent, fn ($q) => $q->forParent((int) $parent))
            ->when(is_null($parent) && !$isSearch, fn ($q) => $q->noParent())
            ->with(['categoryType'])
            ->withCount(['children']);
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceRouteParams(): array
    {
        /** @var Request */
        $request = request();
        /** @var Route $route */
        $route = $request->route();

        return [
            'parent' => $route->parameter('parent'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            'id' => fn ($row) => $row->id,
            'label' => fn ($row) => static::renderPartial($row, 'label-column'),
            'display_label' => fn ($row) => $row->display_label,
            'category_type' => fn ($row) => $row->categoryType?->title,
            'descriptions' => fn ($row) => static::renderLink(route('collaborate.categories.descriptions.index', ['category' => $row->id]), __('ontology.category_description.descriptions')),
            'works' => fn ($row) => static::renderLink(route('collaborate.works.index', ['topics' => [$row->id]]), __('corpus.work.works')),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        /** @var Route */
        $route = $request->route();
        $parentId = $route->parameter('parent');
        /** @var Category|null */
        $parent = $parentId ? Category::with('ancestorsWithSelf')->find($parentId) : null;

        return [
            'forResourceTitle' => __('nav.topics'),
            'forResourceRoute' => route('collaborate.ontology.categories.index'),
            'title' => $parent?->ancestorsWithSelf->implode('display_label', ' > '),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        /** @var Route */
        $route = $request->route();

        return [
            'parentId' => $route->parameter('parent'),
        ];
    }

    protected function preStore(Model $model, Request $request): void
    {
        /** @var Category $model */
        $model->level = ($model->parent?->level ?? 0) + 1;
    }

    protected function redirectAfterStore(Model $model): string
    {
        $route = static::resourceRoute();

        /** @var Category $model */
        /** @var string */
        return route("{$route}.index", ['parent' => $model->parent_id]);
    }

    protected function redirectAfterDestroy(Model $model): string
    {
        return $this->redirectAfterStore($model);
    }

    protected function redirectAfterUpdate(Model $model): string
    {
        return $this->redirectAfterStore($model);
    }
}
