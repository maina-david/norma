<?php

namespace App\Http\Controllers\Ontology\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Ontology\LegalDomainLocationRequest;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\Pivots\LegalDomainLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LegalDomainLocationController extends CollaborateController
{
    protected bool $searchable = false;
    protected bool $withShow = false;
    protected bool $withUpdate = false;
    protected bool $withCreate = true;

    /** @var string */
    protected string $sortBy = 'title';

    /**
     * {@inheritDoc}
     */
    protected static function forResource(): ?string
    {
        return LegalDomain::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function pivotRelationName(): string
    {
        return 'locations';
    }

    protected static function pivotClassName(): ?string
    {
        return LegalDomainLocation::class;
    }

    protected static function resource(): string
    {
        return Location::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceFormRequest(): string
    {
        return LegalDomainLocationRequest::class;
    }

    protected function permissionPrefix(): string
    {
        return 'collaborate.ontology.legal-domain.locations';
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
        /** @var LegalDomain $domain */
        $domain = $this->getForResource();

        $locations = $domain->locations()->select([(new Location())->qualifyColumn('id')]);

        return Location::with('ancestorsWithSelf')->whereIn('id', $locations);
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
    protected static function resourceRoute(): string
    {
        return 'collaborate.legal-domains.jurisdictions';
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceRouteParams(): array
    {
        /** @var Request $request */
        $request = request();

        return [
            'domain' => $request->route('domain'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'headings' => false,
            'domain' => $this->getForResource(),
            'view' => 'pages.ontology.collaborate.legal-domain.show-layout',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        return [
            'domain' => $this->getForResource(),
            'langFile' => 'ontology.legal_domain.locations',
            'formWidth' => 'w-full',
        ];
    }
}
