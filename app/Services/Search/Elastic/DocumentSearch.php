<?php

namespace App\Services\Search\Elastic;

use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

/**
 * Service for searching legislation with elasticsearch.
 **/
class DocumentSearch
{
    /**
     * @param DocumentSearchIndices $indices
     * @param Client                $client
     **/
    public function __construct(protected DocumentSearchIndices $indices, protected Client $client)
    {
    }

    /**
     * @param string        $query
     * @param array<int>    $limitToIds
     * @param int           $from
     * @param int           $size
     * @param array<string> $fields
     * @param array<mixed>  $source
     *
     * @return array<mixed>
     **/
    public function searchReferences(
        string $query,
        array $limitToIds = [],
        int $from = 0,
        int $size = 50,
        array $fields = [],
        array $source = []
    ) {
        $params = $this->getParams($query, $limitToIds, $from, $size, $fields, $source);

        try {
            return $this->client->search($params);
        } catch (BadRequest400Exception $th) {
            return [];
        }
    }

    /**
     * @param string        $query
     * @param array<int>    $limitToIds
     * @param int           $from
     * @param int           $size
     * @param array<string> $fields
     * @param array<mixed>  $source
     *
     * @return array<mixed>
     **/
    public function getReferenceHighlights(
        string $query,
        array $limitToIds = [],
        int $from = 0,
        int $size = 50,
        array $fields = [],
        array $source = []
    ) {
        $params = $this->getParams($query, $limitToIds, $from, $size, $fields, $source);

        $highlightFields = array_map(function ($field) {
            return [$field => ['number_of_fragments' => 3, 'fragment_size' => 100]];
        }, $this->indices->getAllContentFields());
        $params['body']['highlight'] = [
            'force_source' => true,
            'pre_tags' => '<em class="search-result-highlighted">',
            'post_tags' => '</em>',
            'fields' => $highlightFields,
            'max_analyzed_offset' => 999999,
        ];

        try {
            return $this->client->search($params);
        } catch (BadRequest400Exception $th) {
            return [];
        }
    }

    /**
     * @param string  $searchQuery
     * @param Builder $query
     * @param int     $page
     * @param int     $perPage
     *
     * @return LengthAwarePaginator<Reference>
     */
    public function getReferencesForRequirementsSearch(string $searchQuery, Builder $query, int $page = 1, int $perPage = 50): LengthAwarePaginator
    {
        $allIds = $query->get(['id'])->pluck('id')->toArray();
        $items = (new Reference())->newCollection();

        if (empty($allIds)) {
            // @codeCoverageIgnoreStart
            return new LengthAwarePaginator($items, 0, $perPage, 1);
            // @codeCoverageIgnoreEnd
        }

        $from = ($page - 1) * $perPage;
        $results = $this->getReferenceHighlights($searchQuery, $allIds, $from, $perPage);

        if (isset($results['hits'])) {
            if (isset($results['hits']['hits'])) {
                $ids = [];
                $order = [];
                foreach ($results['hits']['hits'] as $index => $hit) {
                    $id = (int) $hit['_id'];
                    $ids[] = $id;
                    $order[$id] = $index;
                }
                /** @var Collection<int,Reference> */
                $items = $query->whereKey($ids)->get()->keyBy('id');
                foreach ($results['hits']['hits'] as $hit) {
                    $highlights = data_get($hit, 'highlight.*', []);
                    $id = (int) $hit['_id'];
                    if (!isset($items[$id])) {
                        // @codeCoverageIgnoreStart
                        continue;
                        // @codeCoverageIgnoreEnd
                    }
                    $items[$id]->_searchHighlights = $highlights[0] ?? [];
                    $items[$id]->_searchScore = $hit['_score'] ?? null;
                }
                $items = $items->sortByDesc(function ($i) {
                    return $i->_searchScore;
                });
            }

            return new LengthAwarePaginator($items, $results['hits']['total']['value'], $perPage, $page);
        }

        // @codeCoverageIgnoreStart
        return new LengthAwarePaginator($items, 0, $perPage, 1);
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param LengthAwarePaginator<Work> $works
     * @param string                     $searchQuery
     *
     * @return LengthAwarePaginator<Work>
     */
    public function addHighlightsToResults(LengthAwarePaginator $works, string $searchQuery): LengthAwarePaginator
    {
        $refIds = [];
        foreach ($works as $work) {
            $refIds = array_merge($refIds, $work->references->modelKeys());
            foreach ($work->children as $child) {
                $refIds = array_merge($refIds, $child->references->modelKeys());
            }
        }
        $results = $this->getReferenceHighlights($searchQuery, $refIds, 1, 9999);
        $refHighlights = [];

        if (isset($results['hits'])) {
            if (isset($results['hits']['hits'])) {
                foreach ($results['hits']['hits'] as $hit) {
                    $highlights = data_get($hit, 'highlight.*', []);
                    $id = (int) $hit['_id'];
                    $refHighlights[$id] = $highlights[0] ?? [];
                }
            }

            foreach ($works as $work) {
                foreach ($work->references as $ref) {
                    $ref->_searchHighlights = $refHighlights[$ref->id] ?? [];
                }
                foreach ($work->children as $child) {
                    foreach ($child->references as $ref) {
                        $ref->_searchHighlights = $refHighlights[$ref->id] ?? [];
                    }
                }
            }
        }

        return $works;
    }

    /**
     * @param string        $query
     * @param array<int>    $limitToIds
     * @param int           $from
     * @param int           $size
     * @param array<string> $fields
     * @param array<mixed>  $source
     *
     * @return array<mixed>
     */
    private function getParams(
        string $query,
        array $limitToIds = [],
        int $from = 0,
        int $size = 20,
        array $fields = [],
        array $source = []
    ): array {
        $query = $this->convertToQueryString($query);

        $defaultFields = [];
        foreach ($this->indices->getAllTitleFields() as $field) {
            $defaultFields[] = $field . '^5';
        }
        foreach ($this->indices->getAllContentFields() as $field) {
            $defaultFields[] = $field;
        }
        $defaultFields[] = 'tags^3';

        $fields = empty($fields) ? $defaultFields : $fields;

        $params = [
            'index' => $this->indices->getReferencesCurrentIndexName(),
            'body' => [
                'from' => $from,
                'size' => $size,
                '_source' => $source,
                'query' => [
                    'bool' => [
                        'must' => [
                            'query_string' => [
                                'query' => $query,
                                'fields' => $fields,
                                'fuzziness' => 'auto',
                                'fuzzy_prefix_length' => 2,
                                'fuzzy_max_expansions' => 15,
                                'allow_leading_wildcard' => false,
                                'auto_generate_synonyms_phrase_query' => false,
                                'default_operator' => 'AND',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if (count($limitToIds) > 0) {
            $params['body']['query']['bool']['filter'] = [
                'ids' => [
                    'values' => $limitToIds,
                ],
            ];
        }

        return $params;
    }

    /**
     * @param string $query
     *
     * @return string
     */
    public function convertToQueryString(string $query): string
    {
        // @phpstan-ignore-next-line
        $query = strtolower(preg_replace('!\s+!', ' ', trim($query)));

        // if there's only one word, force a fuzzy search
        // if (stripos($query, ' ') === false && stripos($query, '~') === false) {
        //    $query = $query . '~';
        // }

        return $query;
    }

    /**
     * @param int $id
     *
     * @return array<mixed>
     */
    public function getReference(int $id): array
    {
        $index = $this->indices->getReferencesCurrentIndexName();

        return $this->client->get([
            'id' => $id,
            'index' => $index,
        ]);
    }

    /**
     * @return array<mixed>
     */
    public function getIndexedWorks(): array
    {
        $this->client->search();
        $params = [
            'index' => $this->indices->getReferencesCurrentIndexName(),
            'body' => [
                'size' => 0,
                'aggs' => [
                    'unique_works' => [
                        'terms' => [
                            'field' => 'work_id',
                            'size' => 1000000,
                        ],
                    ],
                ],
            ],
        ];

        $res = $this->client->search($params);

        $ids = Arr::pluck($res['aggregations']['unique_works']['buckets'], 'key');

        return $ids;
    }

    /**
     * Return all works that have not been indexed by ID.
     *
     * @return array<int>
     */
    public function getUnindexedWorks(): array
    {
        $size = 10000;
        $params = [
            'index' => $this->indices->getReferencesCurrentIndexName(),
            'scroll' => '10s',
            'body' => [
                'size' => $size,
                '_source' => ['work_id'],
            ],
        ];

        $params['scroll'] = '10s';
        $res = $this->client->search($params);
        $scrollId = $res['_scroll_id'];
        $uniqueIds = [];
        while (count($res['hits']['hits']) > 0) {
            foreach ($res['hits']['hits'] as $hit) {
                $uniqueIds[$hit['_source']['work_id']] = true;
            }

            $res = $this->client->scroll(['body' => ['scroll_id' => $scrollId, 'scroll' => $params['scroll']]]);
            $scrollId = $res['_scroll_id'];
        }

        $indexedWorkIds = array_keys($uniqueIds);
        $unindexedIds = [];
        Work::active()
            ->whereHas('references', function ($q) {
                $q->whereRelation('htmlContent', fn ($q) => $q->whereNotNull('cached_content'))
                    ->active()
                    ->typeCitation();
            })
            ->select('id')
            ->orderBy('id')
            ->chunk(2000, function ($works) use (&$unindexedIds, $indexedWorkIds) {
                foreach ($works as $work) {
                    if (!in_array($work->id, $indexedWorkIds)) {
                        $unindexedIds[] = $work->id;
                    }
                }
            });

        return $unindexedIds;
    }
}
