<?php

namespace App\Http\Controllers\Api\V3\Corpus;

use App\Http\Controllers\Api\V3\ApiV3Controller;
use App\Http\Resources\Corpus\Work\V3\WorkResource;
use App\Models\Corpus\Work;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class WorkController extends ApiV3Controller
{
    protected int $maxPerPage = 500;

    /**
     * {@inheritDoc}
     */
    protected function routeParam(): string
    {
        return 'work';
    }

    /**
     * {@inheritDoc}
     */
    protected function model(): string
    {
        return Work::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function apiResource(): string
    {
        return WorkResource::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)
            ->with([
                'children' => fn ($query) => $this->applyOrganisationOrStreamFilter($query, $request)->select(['id']),
            ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function applyOrganisationFilter(Builder $builder, int $organisationId): Builder
    {
        return $builder->whereRelation('normas', 'organisation_id', $organisationId);
    }

    /**
     * {@inheritDoc}
     */
    protected function applyStreamsFilter(Builder $builder, array $streamIds): Builder
    {
        return $builder->whereHas('normas', fn ($query) => $query->whereKey($streamIds));
    }
}
