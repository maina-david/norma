<?php

namespace App\Http\Controllers\Workflows\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Workflows\TaskTypeRequest;
use App\Models\Workflows\RatingGroup;
use App\Models\Workflows\TaskRoute;
use App\Models\Workflows\TaskType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TaskTypeController extends CollaborateController
{
    protected string $sortBy = 'name';

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return TaskType::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.task-types';
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceFormRequest(): string
    {
        return TaskTypeRequest::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            'name' => fn ($row) => static::renderPartial($row, 'name-column'),
            'rating_group' => fn ($row) => static::renderPartial($row, 'rating-group-column'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)->with(['ratingGroup']);
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-5xl',
            'ratingGroups' => RatingGroup::pluck('name', 'id')->toArray(),
            'taskRoutes' => TaskRoute::pluck('name', 'id')->toArray(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function editViewData(Request $request): array
    {
        return $this->createViewData($request);
    }

    /**
     * {@inheritDoc}
     */
    protected function showViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-3xl',
        ];
    }
}
