<?php

namespace App\Http\Controllers\Workflows\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Workflows\RatingGroupRequest;
use App\Models\Workflows\RatingGroup;
use App\Models\Workflows\RatingType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class RatingGroupController extends CollaborateController
{
    /** @var string */
    protected string $sortBy = 'name';

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return RatingGroup::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.rating-groups';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return RatingGroupRequest::class;
    }

    /**
     * Get the model columns that should be displayed in the index table.
     *
     * @return array<string|int, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'name',
            'description',
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)->with(['ratingTypes']);
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        return [
            'ratingTypes' => RatingType::pluck('name', 'id')->toArray(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function editViewData(Request $request): array
    {
        return [
            'ratingTypes' => RatingType::pluck('name', 'id')->toArray(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function postStore(Model $model, Request $request): void
    {
        if ($request->has('rating_types')) {
            $types = $request->get('rating_types');
            /** @var RatingGroup $model */
            $model->ratingTypes()->sync($types);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function postUpdate(Model $model, Request $request): void
    {
        $this->postStore($model, $request);
    }
}
