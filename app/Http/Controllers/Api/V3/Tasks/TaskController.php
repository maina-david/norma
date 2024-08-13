<?php

namespace App\Http\Controllers\Api\V3\Tasks;

use App\Http\Controllers\Api\V3\ApiV3Controller;
use App\Http\Resources\Tasks\Task\V3\TaskResource;
use App\Models\Tasks\Task;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TaskController extends ApiV3Controller
{
    /**
     * {@inheritDoc}
     */
    protected function routeParam(): string
    {
        return 'task';
    }

    /**
     * {@inheritDoc}
     */
    protected function model(): string
    {
        return Task::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function apiResource(): string
    {
        return TaskResource::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(Request $request): Builder
    {
        /** @var string $statuses */
        $statuses = $request->query('statuses');

        return parent::baseQuery($request)
            ->when($statuses, fn ($query) => $query->whereIn('task_status', explode(',', $statuses)));
    }

    /**
     * {@inheritDoc}
     */
    protected function applyOrganisationFilter(Builder $builder, int $organisationId): Builder
    {
        return $builder->whereRelation('libryo', 'organisation_id', $organisationId);
    }

    /**
     * {@inheritDoc}
     */
    protected function applyStreamsFilter(Builder $builder, array $streamIds): Builder
    {
        return $builder->whereIn('place_id', $streamIds);
    }
}
