<?php

namespace App\Http\Controllers\Api\V3\Corpus;

use App\Http\Controllers\Api\V3\ApiV3Controller;
use App\Http\Resources\Corpus\Reference\V3\ReferenceResource;
use App\Models\Corpus\Reference;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RequirementController extends ApiV3Controller
{
    /**
     * {@inheritDoc}
     */
    protected function routeParam(): string
    {
        return 'requirement';
    }

    /**
     * {@inheritDoc}
     */
    protected function model(): string
    {
        return Reference::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function apiResource(): string
    {
        return ReferenceResource::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(Request $request): Builder
    {
        /** @var string $works */
        $works = $request->query('works');

        return parent::baseQuery($request)
            ->hasActiveWork()
            ->with([
                'raisesConsequenceGroups' => fn ($query) => $query->select(['id']),
                'refPlainText' => fn ($query) => $query->select(['reference_id', 'plain_text']),
                'htmlContent' => fn ($query) => $query->select(['reference_id', 'cached_content']),
            ])
            ->when($works, fn ($query) => $query->whereIn('work_id', explode(',', $works)));
    }

    /**
     * {@inheritDoc}
     */
    protected function applyUpdatedAtFilter(Builder $builder, Request $request): void
    {
        if ($date = $this->getUpdatedAtFilter($request)) {
            $builder->whereRelation('htmlContent', 'updated_at', '>=', $date);
        }
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
