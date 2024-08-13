<?php

namespace App\Http\Controllers\Api\V3\Assess;

use App\Http\Controllers\Api\V3\ApiV3Controller;
use App\Http\Resources\Assess\AssessmentItem\V3\AssessmentItemResource;
use App\Models\Assess\AssessmentItem;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AssessmentItemController extends ApiV3Controller
{
    protected int $maxPerPage = 50;

    /**
     * {@inheritDoc}
     */
    protected function routeParam(): string
    {
        return 'assessment_item';
    }

    /**
     * {@inheritDoc}
     */
    protected function model(): string
    {
        return AssessmentItem::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function apiResource(): string
    {
        return AssessmentItemResource::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function applyOrganisationFilter(Builder $builder, int $organisationId): Builder
    {
        return $builder->whereRelation('assessmentResponses.libryo', 'organisation_id', $organisationId)
            ->with([
                'assessmentResponses' => fn ($query) => $query->whereRelation('libryo', 'organisation_id', $organisationId),
            ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function applyStreamsFilter(Builder $builder, array $streamIds): Builder
    {
        return $builder->whereHas('assessmentResponses.libryo', fn ($query) => $query->whereKey($streamIds))
            ->with([
                'assessmentResponses' => fn ($query) => $query->whereIn('place_id', $streamIds),
            ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function applyUpdatedAtFilter(Builder $builder, Request $request): void
    {
        if ($date = $this->getUpdatedAtFilter($request)) {
            $builder->whereRelation('assessmentResponses', 'updated_at', '>=', $date);
        }
    }
}
