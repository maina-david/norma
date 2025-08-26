<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiMultiGetRequest;
use App\Models\Traits\ApiQueryFilterable;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class AbstractApiController extends Controller
{
    /**
     * Default per page.
     *
     * @var int
     */
    protected $perPage = 50;

    /**
     * The current model.
     *
     * @var Model
     */
    protected Model $model;

    /**
     * The processed includes after any non allowedIncludes have been filtered out.
     *
     * @var array<string>
     */
    public array $includes = [];

    // /**
    //  * Get the form request to be used to validate the models.
    //  *
    //  * @return string
    //  */
    // abstract protected function getFormRequestClass(): string;

    /**
     * Get the eloquent model to be used.
     *
     * @return string
     */
    abstract protected function getModelClass(): string;

    // /**
    //  * Get the current request.
    //  *
    //  * @return FormRequest
    //  */
    // protected function getRequest()
    // {
    //     return app($this->getFormRequestClass());
    // }

    /**
     * Get the api resource to be used to format the response.
     *
     * @return string
     */
    abstract protected function getApiResourceClass(): string;

    /**
     * Get the allowed includes.
     *
     * @return array<string>
     */
    protected function getAllowedIncludes(): array
    {
        // @codeCoverageIgnoreStart
        return [];
        // @codeCoverageIgnoreEnd
    }

    /**
     * Add the query parameters and prepare the listing response.
     *
     * @param ApiMultiGetRequest $request
     * @param Builder            $query
     * @param int|null           $page
     *
     * @throws Exception
     *
     * @return Collection<Model>|LengthAwarePaginator<Model>
     */
    protected function processListingQuery(Request $request, Builder $query, ?int $page = null): Collection|LengthAwarePaginator
    {
        /** @var array<string> */
        $traits = class_uses($query->getModel());
        if (!in_array(ApiQueryFilterable::class, $traits)) {
            // @codeCoverageIgnoreStart
            throw new Exception('Please make sure ' . $this->getModelClass() . ' uses the ' . ApiQueryFilterable::class . ' trait');
            // @codeCoverageIgnoreEnd
        }

        $this->addIncludesToQuery($request, $query);
        $query->apiQueryFilter($request);

        $this->addCountsToQuery($request, $query);

        /** @var Collection<Model>|LengthAwarePaginator<Model> */
        return $request->getPageFromQueryParams()
            ? $query->paginate(
                $request->getPerPageFromQueryParams() ?: $this->perPage,
                $request->getFieldsFromQueryParams(),
                'page',
                $page,
            )
            : $query->get($request->getFieldsFromQueryParams());
    }

    /**
     * @param Request $request
     * @param Builder $query
     *
     * @return Model
     */
    protected function processSingleQuery(Request $request, Builder $query): Model
    {
        $this->addIncludesToQuery($request, $query);
        $this->addCountsToQuery($request, $query);

        /** @var Model */
        return $query->firstOrFail($request->getFieldsFromQueryParams());
    }

    /**
     * @param Request $request
     * @param Builder $query
     *
     * @return array<string>
     */
    protected function addIncludesToQuery(Request $request, Builder $query): array
    {
        $includes = array_intersect($request->getIncludesFromQueryParams(), $this->getAllowedIncludes());
        $this->includes = array_merge($this->includes, array_diff($includes, array_keys($query->getEagerLoads())));

        if (!empty($this->includes)) {
            $with = [];
            foreach ($this->includes as $inc) {
                // TODO: once needed uncomment this. used in AssessmentItemController in norma-api.
                // But seeing that it's not being used, we might not need it at all
                if (method_exists($this, 'getCustomIncludesQueries') && isset($this->getCustomIncludesQueries()[$inc])) {
                    $query->with([$inc => $this->getCustomIncludesQueries()[$inc]]);
                } else {
                    $with[] = $inc;
                }
            }
            if (count($with) !== 0) {
                $query->with($with);
            }
        }

        return $this->includes;
    }

    /**
     * @param Request $request
     * @param Builder $query
     *
     * @return void
     */
    private function addCountsToQuery(Request $request, Builder $query): void
    {
        $counts = $request->getCountsFromQueryParams();
        if (!empty($counts)) {
            $query->withCount($counts);
        }
    }

    /**
     * Get base query for model.
     *
     * @param Request $request
     *
     * @return Builder
     */
    public function getQuery(Request $request): Builder
    {
        // @codeCoverageIgnoreStart
        if (!isset($this->model)) {
            $className = $this->getModelClass();
            /** @var Model $model */
            $model = new $className();
            $this->model = $model;
        }

        return $this->model->newQuery();
        // @codeCoverageIgnoreEnd
    }

    /**
     * Display a listing of the resource.
     *
     * @param ApiMultiGetRequest $request
     * @param Builder            $query
     *
     * @return ResourceCollection<Model>
     */
    public function index(Request $request, ?Builder $query = null): ResourceCollection
    {
        $query ??= $this->getQuery($request);
        $results = $this->processListingQuery($request, $query);

        $collectionClass = null;
        if (method_exists($this, 'getApiResourceCollectionClass')) {
            $collectionClass = $this->getApiResourceCollectionClass();
            $resource = new $collectionClass($results);
        } else {
            $resourceClass = $this->getApiResourceClass();
            $resource = $resourceClass::collection($results);
        }

        return $resource->preserveQuery();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param mixed $modelId
     *
     * @return JsonResource
     */
    // public function store()
    // {
    //     $request = $this->getRequest();

    //     $model = $this->getModelClass();

    //     $this->model = new $model();

    //     $this->model->fill($request->only(array_keys($request->rules())));

    //     $this->prePersist();

    //     $this->model->save();

    //     $this->postPersist();

    //     return $this->successfulPersistResponse();
    // }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param string  $id
     * @param Builder $query
     *
     * @return JsonResource
     */
    public function show(Request $request, string $id, ?Builder $query = null)
    {
        $query ??= $this->getQuery($request);

        $query->whereKey((int) $id);

        $this->preShow((int) $id);

        /** @var Model */
        $model = $this->processSingleQuery($request, $query);
        $this->model = $model;

        $this->postShow();

        $resourceClass = $this->getApiResourceClass();
        /** @var JsonResource $resource */
        $resource = new $resourceClass($this->model);

        return $resource;
    }

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param int $modelId
    //  *
    //  * @return JsonResource
    //  */
    // public function update($modelId)
    // {
    //     $request = $this->getRequest();

    //     /** @var Model */
    //     $model = $this->model->findOrFail($modelId);
    //     $this->model = $model;

    //     $this->model->fill($request->only(array_keys($request->rules())));

    //     $this->prePersist();

    //     $this->model->save();

    //     $this->postPersist();

    //     return $this->successfulPersistResponse();
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param int $modelId
    //  *
    //  * @return \Illuminate\Http\Resources\Json\JsonResource
    //  */
    // public function destroy($modelId)
    // {
    //     /** @var Model */
    //     $model = $this->model->findOrFail($modelId);
    //     $this->model = $model;

    //     $this->preDestroy();

    //     $this->model->delete();

    //     $this->postDestroy();

    //     return $this->successfulDestroyResponse();
    // }

    /**
     * Pre show hook.
     *
     * @return void
     */
    protected function preShow(int $modelId): void
    {
    }

    /**
     * Post show hook.
     *
     * @return void
     */
    protected function postShow(): void
    {
    }

    // /**
    //  * Pre persist hook.
    //  *
    //  * @return void
    //  */
    // protected function prePersist(): void
    // {
    // }

    // /**
    //  * Post persist hook.
    //  *
    //  * @return void
    //  */
    // protected function postPersist(): void
    // {
    // }

    // /**
    //  * Pre Destroy hook.
    //  *
    //  * @return void
    //  */
    // protected function preDestroy(): void
    // {
    // }

    // /**
    //  * Post Destroy hook.
    //  *
    //  * @return void
    //  */
    // protected function postDestroy(): void
    // {
    // }

    // /**
    //  * Successful persist response.
    //  *
    //  * @return JsonResource
    //  */
    // protected function successfulPersistResponse(): JsonResource
    // {
    //     return (new $this->resource($this->model))
    //         ->additional(['meta' => [
    //             'message' => __('common.saved_successfully'),
    //         ]]);
    // }

    // /**
    //  * Successful persist response.
    //  *
    //  * @return JsonResource
    //  */
    // protected function successfulDestroyResponse(): JsonResource
    // {
    //     return (new $this->resource($this->model))
    //         ->additional(['meta' => [
    //             'message' => __('common.deleted_successfully'),
    //         ]]);
    // }

    // private function getClassName(object $class): string
    // {
    //     return class_basename(get_class($class));
    // }
}
