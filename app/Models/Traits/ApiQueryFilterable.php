<?php

namespace App\Models\Traits;

use App\Http\Requests\ApiMultiGetRequest;
use App\Http\Services\Api\ApiResourceQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ApiQueryFilterable
{
    /**
     * @param Builder      $query
     * @param Request|null $request
     *
     * @return Builder
     */
    public function scopeApiQueryFilter(Builder $query, ?Request $request = null): Builder
    {
        /** @var ApiMultiGetRequest $request */
        $request = $request ?? app(Request::class);

        $resourceQueryBuilder = app(ApiResourceQueryBuilder::class);

        return $resourceQueryBuilder->buildFilteredQuery($query, $request);
    }
}
