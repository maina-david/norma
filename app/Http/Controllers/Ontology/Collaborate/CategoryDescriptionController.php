<?php

namespace App\Http\Controllers\Ontology\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Ontology\CategoryDescriptionRequest;
use App\Models\Ontology\Category;
use App\Models\Ontology\CategoryDescription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CategoryDescriptionController extends CollaborateController
{
    protected bool $withShow = false;

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return CategoryDescription::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceFormRequest(): string
    {
        return CategoryDescriptionRequest::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function forResource(): ?string
    {
        return Category::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.categories.descriptions';
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            'description' => fn ($row) => $row->description,
            'jurisdiction' => fn ($row) => $row->location->title ?? '-',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        /** @var Category $category */
        $category = $this->getForResource();

        /** @var string $descriptions */
        $descriptions = __('ontology.category_description.descriptions');

        return [
            'forResourceTitle' => "{$category->display_label} {$descriptions}",
            'forResourceRoute' => route('collaborate.ontology.categories.index', ['parent' => $category->parent_id]),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function editViewData(Request $request): array
    {
        return [
            'category' => $this->getForResource(),
            'formWidth' => 'w-full',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        return [
            'category' => $this->getForResource(),
            'formWidth' => 'w-full',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceRouteParams(): array
    {
        /** @var Request $request */
        $request = request();

        return [
            'category' => $request->route('category'),
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
        $category = $this->getForResource();

        return $category->descriptions()->with(['location'])->getQuery();
    }

    /**
     * Which route to redirect to after store action.
     *
     * @param CategoryDescription $model
     *
     * @return string
     */
    protected function redirectAfterStore(Model $model): string
    {
        return route('collaborate.categories.descriptions.index', ['category' => $model->category_id]);
    }

    /**
     * Which route to redirect to after update action.
     *
     * @param CategoryDescription $model
     *
     * @return string
     */
    protected function redirectAfterUpdate(Model $model): string
    {
        return $this->redirectAfterStore($model);
    }

    /**
     * Which route to redirect to after destroy action.
     *
     * @param CategoryDescription $model
     *
     * @return string
     */
    protected function redirectAfterDestroy(Model $model): string
    {
        return $this->redirectAfterStore($model);
    }
}
