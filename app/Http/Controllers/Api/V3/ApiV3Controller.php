<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use App\Models\Customer\Norma;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Throwable;

abstract class ApiV3Controller extends Controller
{
    /** @var int */
    protected int $maxPerPage = 100;

    /** @var string */
    protected string $orderBy = 'id';

    /** @var string */
    protected string $orderByDirection = 'asc';

    /**
     * Get the route parameter to be used to access the show/update/delete endpoints.
     *
     * @return string
     */
    abstract protected function routeParam(): string;

    /**
     * Get the model class to be used.
     *
     * @return class-string
     */
    abstract protected function model(): string;

    /**
     * Get the api resource to be used.
     *
     * @return class-string
     */
    abstract protected function apiResource(): string;

    /**
     * Apply necessary filters to the query builder for the given organisation.
     *
     * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
     * @param int                                             $organisationId
     *
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    abstract protected function applyOrganisationFilter(Builder $builder, int $organisationId): Builder;

    /**
     * Apply necessary filters to the query builder for the given streams.
     *
     * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
     * @param array<int, string>                              $streamIds
     *
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    abstract protected function applyStreamsFilter(Builder $builder, array $streamIds): Builder;

    /**
     * Get the active organisation ID.
     *
     * @return int
     */
    protected function activeOrganisationID(): int
    {
        /** @var int */
        return auth('api')->client()->organisation_id;
    }

    /**
     * Get the stream filter from the request.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $org
     *
     * @return array<int, string>
     */
    protected function getStreamsFromRequest(Request $request, int $org): array
    {
        /** @var string|null $streams */
        $streams = $request->get('streams');

        return $streams ? Norma::whereKey(explode(',', $streams))->where('organisation_id', $org)->pluck('id')->all() : [];
    }

    /**
     * Get the updated at filter.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Carbon\Carbon|null
     */
    protected function getUpdatedAtFilter(Request $request): ?Carbon
    {
        if ($date = $request->get('updatedAfter')) {
            try {
                return Carbon::parse($date);
                // @codeCoverageIgnoreStart
            } catch (Throwable) {
                return null;
            }
            // @codeCoverageIgnoreEnd
        }

        return null;
    }

    /**
     * Apply the updated at filter.
     *
     * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
     * @param \Illuminate\Http\Request                        $request
     *
     * @return void
     */
    protected function applyUpdatedAtFilter(Builder $builder, Request $request): void
    {
        if ($date = $this->getUpdatedAtFilter($request)) {
            $builder->where('updated_at', '>=', $date);
        }
    }

    /**
     * The base query for the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->model();

        $query = (new $model())->newQuery();
        $this->applyUpdatedAtFilter($query, $request);

        return $this->applyOrganisationOrStreamFilter($query, $request);
    }

    /**
     * Apply the relevant filter.
     *
     * @param \Illuminate\Contracts\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request                        $request
     *
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    protected function applyOrganisationOrStreamFilter(Builder $query, Request $request): Builder
    {
        $organisation = $this->activeOrganisationID();
        $streams = $this->getStreamsFromRequest($request, $organisation);

        return empty($streams) ? $this->applyOrganisationFilter($query, $organisation) : $this->applyStreamsFilter($query, $streams);
    }

    /**
     * Get the listing query.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = $this->baseQuery($request);

        $perPage = $request->get('perPage', $this->maxPerPage);

        $results = $query->orderBy($this->orderBy, $this->orderByDirection)->paginate(min($perPage, $this->maxPerPage));

        return $this->toCollectionResponse($request, $results);
    }

    /**
     * Convert the results to a json response.
     *
     * @param \Illuminate\Http\Request                    $request
     * @param \Illuminate\Pagination\LengthAwarePaginator $results
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function toCollectionResponse(Request $request, LengthAwarePaginator $results): JsonResponse
    {
        /** @var \Illuminate\Http\Resources\Json\JsonResource $resource */
        $resource = $this->apiResource();

        $paginated = $resource::collection($results)
            ->resource
            ->appends($request->query())
            ->toArray($request);

        return response()->json([
            'data' => $paginated['data'],
            'links' => [
                'first' => $paginated['first_page_url'] ?? null,
                'last' => $paginated['last_page_url'] ?? null,
                'prev' => $paginated['prev_page_url'] ?? null,
                'next' => $paginated['next_page_url'] ?? null,
            ],
            'meta' => Arr::except($paginated, [
                'data',
                'links',
                'first_page_url',
                'last_page_url',
                'prev_page_url',
                'next_page_url',
            ]),
        ]);
    }

    /**
     * View the given resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function show(Request $request): JsonResource
    {
        $modelKey = $request->route($this->routeParam());

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->baseQuery($request)->findOrFail($modelKey);

        /** @var \Illuminate\Http\Resources\Json\JsonResource $resource */
        $resource = $this->apiResource();

        return new $resource($model);
    }
}
