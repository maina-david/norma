<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Contracts\Http\ResourceAction;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\ResourceActions\Corpus\SyncCatalogueToWork;
use App\Models\Corpus\CatalogueWork;
use App\Traits\Arachno\UsesSourceFilter;
use App\Traits\Geonames\UsesJurisdictionFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogueWorkController extends CollaborateController
{
    use UsesJurisdictionFilter;
    use UsesSourceFilter;

    /** @var bool */
    protected bool $sortable = true;

    /** @var bool */
    protected bool $searchable = true;

    /** @var string */
    protected string $sortBy = 'title';

    /** @var string */
    protected string $searchResultKey = 'id';

    /** @var bool */
    protected bool $withCreate = false;

    /** @var bool */
    protected bool $withShow = false;

    /** @var bool */
    protected bool $withUpdate = false;

    /** @var bool */
    protected bool $withDelete = true;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @phpstan-return class-string
     *
     * @return string
     */
    protected static function resource(): string
    {
        return CatalogueWork::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.catalogue-works';
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
     * Use the dot notation for relations and for custom results use functions that
     * will receive the current row as an arg. The field name will be used as the
     * translation key but when you use functions, make sure the array is keyed with the
     * translation name.
     *
     * e.g. ['profile' => 'profile.id', 'name'].
     *
     * @return array<string|int, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'id',
            'title' => fn ($row) => static::renderPartial($row, 'title-column'),
            'work' => fn ($row) => static::renderPartial($row, 'work-column'),
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)
            ->with(['expressions', 'work']);
    }

    /**
     * Get the resource filters.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function resourceFilters(): array
    {
        return [
            'jurisdiction' => $this->getJurisdictionFilter(),
            'source' => $this->getSourceFilter(),
        ];
    }

    /**
     * Get the resource actions.
     *
     * @return array<int, ResourceAction>
     */
    protected static function resourceActions(): array
    {
        return [
            new SyncCatalogueToWork(),
        ];
    }
}
