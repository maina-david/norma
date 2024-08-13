<?php

namespace App\Http\Controllers\Arachno\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Arachno\SourceLocationRequest;
use App\Models\Arachno\Pivots\LocationSource;
use App\Models\Arachno\Source;
use App\Models\Geonames\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SourceLocationController extends CollaborateController
{
    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return Location::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.sources.jurisdictions';
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceFormRequest(): string
    {
        return SourceLocationRequest::class;
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
    protected static function pivotRelationName(): string
    {
        return 'locations';
    }

    /**
     * {@inheritDoc}
     */
    protected static function forResource(): ?string
    {
        return Source::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function pivotClassName(): ?string
    {
        return LocationSource::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(Request $request): Builder
    {
        /** @var Source $source */
        $source = $this->getForResource();

        return $source->locations()
            ->with('ancestorsWithSelf')
            ->getQuery();
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceRouteParams(): array
    {
        /** @var Request $request */
        $request = request();

        return [
            'source' => $request->route('source'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function permissionPrefix(): string
    {
        return 'collaborate.arachno.sources.jurisdictions';
    }
}
