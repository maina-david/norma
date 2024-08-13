<?php

namespace App\Services\Search\LibryoAI;

use App\Http\Services\LibryoAI\Client;
use App\Models\Corpus\CatalogueDoc;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;

class LibryoAISearchEngine extends Engine
{
    /**
     * @param Client $client
     */
    public function __construct(protected Client $client)
    {
    }

    /**
     * Update the given model in the index.
     *
     * @param Collection $models
     *
     * @return void
     */
    public function update($models): void
    {
        $endpoint = $models->first()?->searchableAs() ?? '';

        foreach ($models as $model) {
            $payload = $model->toSearchableArray();

            $this->client->updateInIndex($endpoint, $payload);
        }
    }

    /**
     * @param Collection $models
     *
     * @return void
     */
    public function delete($models): void
    {
        $endpoint = $models->first()?->searchableAs() ?? '';

        foreach ($models as $model) {
            $this->client->deleteFromIndex($endpoint, $model->id);
        }
    }

    /**
     * @param Builder $builder
     *
     * @return array<mixed>
     */
    protected function extractFilters(Builder $builder): array
    {
        return $builder->wheres;
    }

    /**
     * @param Builder  $builder
     * @param int|null $perPage
     * @param int      $page
     *
     * @return Collection
     */
    protected function performSearch(Builder $builder, ?int $perPage, int $page): Collection
    {
        /** @var CatalogueDoc $model */
        $model = $builder->model;

        $weights = [
            'text_weight' => 1,
            'keywords_weight' => 20,
            'description_weight' => 1,
            'text_embedding_weight' => 100,
        ];

        if (method_exists($model, 'getSearchWeights')) {
            $weights = $model->getSearchWeights();
        }

        return $this->client->search(
            $model->searchableAs(),
            $builder->query,
            $this->extractFilters($builder),
            $weights,
            $perPage ?? 400,
            ($page - 1) * $perPage
        );
    }

    /**
     * {@inheritDoc}
     */
    public function search(Builder $builder)
    {
        return $this->performSearch($builder, $builder->limit, 1);
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        return $this->performSearch($builder, $perPage, $page);
    }

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function mapIds($results)
    {
        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function map(Builder $builder, $results, $model)
    {
        /** @var Collection<int, int> $results */
        if ($results->isEmpty()) {
            return $model->newCollection();
        }

        $objectIds = $results->all();

        $extracted = $model->getScoutModelsByIds($builder, $objectIds)->keyBy('id');

        /** @var \Illuminate\Database\Eloquent\Collection */
        return $results->map(fn ($item) => $extracted->get($item))->filter()->values();
    }

    /**
     * {@inheritDoc}
     */
    public function lazyMap(Builder $builder, $results, $model)
    {
        if ($results->isEmpty()) {
            return LazyCollection::make($model->newCollection());
        }

        $objectIds = $results->all();

        return $model->queryScoutModelsByIds($builder, $objectIds)->cursor();
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalCount($results)
    {
        return 10000;
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     */
    public function flush($model)
    {
    }

    /**
     * @codeCoverageIgnore
     *
     * @param              $name
     * @param array<mixed> $options
     *
     * @return true
     */
    public function createIndex($name, array $options = [])
    {
        return true;
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     */
    public function deleteIndex($name)
    {
        return true;
    }
}
