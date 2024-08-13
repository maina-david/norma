<?php

namespace App\Services\Search\Meilisearch;

use App\Models\Corpus\Reference;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class Highlighter
{
    /**
     * Get the highlights for the given works.
     *
     * @param \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator $works
     * @param string                                                                     $search
     * @param int                                                                        $highlightLength
     *
     * @return \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function highlightForWorks(Collection|LengthAwarePaginator $works, string $search, int $highlightLength = 100): Collection|LengthAwarePaginator
    {
        $refIds = [];
        foreach ($works as $work) {
            $refIds = array_merge($refIds, $work->references->modelKeys());
            foreach ($work->children as $child) {
                $refIds = array_merge($refIds, $child->references->modelKeys());
            }
        }

        // @codeCoverageIgnoreStart
        if (empty($refIds)) {
            return $works;
        }
        // @codeCoverageIgnoreEnd

        $results = Reference::search($search, function ($engine, string $query, array $options) use ($refIds) {
            $options['attributesToHighlight'] = ['cached_content'];
            $options['highlightPreTag'] = '<em class="search-result-highlighted">';
            $options['highlightPostTag'] = '</em>';
            $options['attributesToCrop'] = ['cached_content'];
            $options['cropLength'] = 20;
            $options['limit'] = count($refIds);

            return $engine->search($query, $options);
        })
            ->whereIn('id', $refIds)
            ->raw();

        $results = collect($results['hits'] ?? [])->mapWithKeys(function ($item) {
            return [
                $item['id'] => [$item['_formatted']['cached_content'] ?? ''],
            ];
        });

        if ($results->isNotEmpty()) {
            foreach ($works as $work) {
                foreach ($work->references as $ref) {
                    $ref->_searchHighlights = $results->get($ref->id) ?? [];
                }
                foreach ($work->children as $child) {
                    foreach ($child->references as $ref) {
                        $ref->_searchHighlights = $results->get($ref->id) ?? [];
                    }
                }
            }
        }

        return $works;
    }
}
