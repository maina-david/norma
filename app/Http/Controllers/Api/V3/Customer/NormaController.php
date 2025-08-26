<?php

namespace App\Http\Controllers\Api\V3\Customer;

use App\Http\Controllers\Api\V3\ApiV3Controller;
use App\Http\Resources\Customer\Norma\V3\NormaResource;
use App\Models\Customer\Norma;
use Illuminate\Contracts\Database\Eloquent\Builder;

class NormaController extends ApiV3Controller
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
        return Norma::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function apiResource(): string
    {
        return NormaResource::class;
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
