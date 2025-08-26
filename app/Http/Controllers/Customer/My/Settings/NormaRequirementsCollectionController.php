<?php

namespace App\Http\Controllers\Customer\My\Settings;

use App\Enums\Application\ApplicationType;
use App\Http\Controllers\Abstracts\CrudController;
use App\Http\Requests\Customer\NormaRequirementsCollectionRequest;
use App\Models\Compilation\RequirementsCollection;
use App\Models\Customer\Norma;
use App\Models\Customer\Pivots\NormaRequirementsCollection;
use App\Stores\Customer\NormaRequirementsCollectionStore;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class NormaRequirementsCollectionController extends CrudController
{
    /** @var bool */
    protected bool $searchable = false;

    /** @var bool */
    protected bool $withShow = false;

    /** @var bool */
    protected bool $withUpdate = false;

    protected bool $withCreate = true;

    protected Norma $norma;

    protected static function application(): ApplicationType
    {
        return ApplicationType::my();
    }

    /**
     * {@inheritDoc}
     */
    protected static function forResource(): ?string
    {
        return Norma::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function pivotRelationName(): string
    {
        return 'requirementsCollections';
    }

    protected static function pivotClassName(): ?string
    {
        return NormaRequirementsCollection::class;
    }

    protected static function resource(): string
    {
        return RequirementsCollection::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceFormRequest(): string
    {
        return NormaRequirementsCollectionRequest::class;
    }

    protected function permissionPrefix(): string
    {
        return 'my.normas.compilation.requirements-collections';
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
        /** @var Norma */
        $norma = $this->getForResource();
        $this->norma = $norma;

        return $norma->requirementsCollections()
            ->with('ancestorsWithSelf')
            ->getQuery();
    }

    /**
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            'title' => fn ($row) => $row->ancestorsWithSelf->implode('title', ' > '),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function authoriseAction(string $action, mixed $arguments = []): void
    {
        $this->authorize('my.normas.compilation.requirements-collections.' . $action);
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'my.settings.normas.compilation.requirements-collections';
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceRouteParams(): array
    {
        /** @var Request $request */
        $request = request();

        return [
            'norma' => $request->route('norma'),
        ];
    }

    protected function getView(string $action): View
    {
        /** @var View $view */
        $view = view("pages.customer.my.norma-requirements-collection.settings.{$action}");

        return $view;
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'headings' => false,
            'norma' => $this->norma,
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        return [
            'norma' => $this->getForResource(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function postStore(Model $forResource, Request $request): void
    {
        if (!$request->boolean('include_ancestors') && !$request->boolean('include_descendants')) {
            return;
        }
        $data = $request->validated();

        foreach ($data['ids'] as $id) {
            /** @var RequirementsCollection|null */
            $collection = RequirementsCollection::find($id);
            if (!$collection) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            if ($request->boolean('include_ancestors')) {
                app(NormaRequirementsCollectionStore::class)
                    ->attachRelations($forResource, 'requirementsCollections', $collection->ancestors);
            }

            if ($request->boolean('include_descendants')) {
                app(NormaRequirementsCollectionStore::class)
                    ->attachRelations($forResource, 'requirementsCollections', $collection->descendants);
            }
        }
    }
}
