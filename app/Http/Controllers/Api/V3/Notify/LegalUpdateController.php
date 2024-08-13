<?php

namespace App\Http\Controllers\Api\V3\Notify;

use App\Http\Controllers\Api\V3\ApiV3Controller;
use App\Http\Resources\Notify\LegalUpdate\V3\LegalUpdateResource;
use App\Models\Notify\LegalUpdate;
use Illuminate\Contracts\Database\Eloquent\Builder;

class LegalUpdateController extends ApiV3Controller
{
    protected int $maxPerPage = 500;

    /**
     * {@inheritDoc}
     */
    protected function routeParam(): string
    {
        return 'update';
    }

    /**
     * {@inheritDoc}
     */
    protected function model(): string
    {
        return LegalUpdate::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function apiResource(): string
    {
        return LegalUpdateResource::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function applyOrganisationFilter(Builder $builder, int $organisationId): Builder
    {
        return $builder->whereRelation('libryos', 'organisation_id', $organisationId)
            ->with([
                'libryos' => fn ($query) => $query->select(['id'])->where('organisation_id', $organisationId),
            ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function applyStreamsFilter(Builder $builder, array $streamIds): Builder
    {
        return $builder->whereHas('libryos', fn ($query) => $query->whereKey($streamIds))
            ->with([
                'libryos' => fn ($query) => $query->select(['id'])->whereKey($streamIds),
            ]);
    }
}
