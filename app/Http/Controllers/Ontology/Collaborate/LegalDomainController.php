<?php

namespace App\Http\Controllers\Ontology\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\ResourceActions\Ontology\ArchiveLegalDomain;
use App\Http\ResourceActions\Ontology\RestoreLegalDomain;
use App\Models\Ontology\LegalDomain;
use App\Traits\UsesArchivedTableFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LegalDomainController extends CollaborateController
{
    use UsesArchivedTableFilter;
    /** @var string */
    protected string $sortBy = 'title';

    /** @var bool */
    protected bool $withCreate = false;

    /** @var bool */
    protected bool $withDelete = false;

    /** @var bool */
    protected bool $withUpdate = false;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return LegalDomain::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.legal-domains';
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
     * @return array<string, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'title' => fn ($row) => static::renderPartial($row, 'title-column'),
            'parent' => fn ($row) => static::renderPartial($row, 'parent-column'),
            'top_parent' => fn ($row) => static::renderPartial($row, 'top-parent-column'),
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
        return parent::baseQuery($request)->with(['parent', 'topParent']);
    }

    /**
     * Get the domains for the selector.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexJson(Request $request): JsonResponse
    {
        /** @var int|null $locationId */
        $locationId = $request->get('location_id');

        $domains = LegalDomain::whereNull('parent_id')
            ->active()
            ->forLocation($locationId)
            ->orderBy('title');

        $domains = $domains->get(['id', 'title'])->all();

        return response()->json($domains);
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceActions(): array
    {
        return [
            new ArchiveLegalDomain(),
            new RestoreLegalDomain(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceFilters(): array
    {
        return [
            ...$this->archivedTableFilter(),
        ];
    }
}
