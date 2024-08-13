<?php

namespace App\Http\Controllers\Api\V3\Customer;

use App\Http\Controllers\Api\V3\ApiV3Controller;
use App\Http\Resources\Customer\Libryo\V3\LibryoResource;
use App\Models\Customer\Libryo;
use Illuminate\Contracts\Database\Eloquent\Builder;

class LibryoController extends ApiV3Controller
{
    /**
     * {@inheritDoc}
     */
    protected function routeParam(): string
    {
        return 'stream';
    }

    /**
     * {@inheritDoc}
     */
    protected function model(): string
    {
        return Libryo::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function apiResource(): string
    {
        return LibryoResource::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function applyOrganisationFilter(Builder $builder, int $organisationId): Builder
    {
        return $builder->where('organisation_id', $organisationId);
    }

    /**
     * {@inheritDoc}
     */
    protected function applyStreamsFilter(Builder $builder, array $streamIds): Builder
    {
        return $builder->whereKey($streamIds);
    }
}
