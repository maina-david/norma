<?php

namespace App\Cache\Ontology\Collaborate;

use App\Cache\Abstracts\ModelCache;
use App\Models\Ontology\LegalDomain;
use Illuminate\Support\Facades\Cache;

class LegalDomainSelectorCache extends ModelCache
{
    /**
     * Get the cache key for the given cache store.
     *
     * @return string
     */
    protected static function cacheKey(): string
    {
        return 'legal_domain_selector_cache';
    }

    /**
     * Store the results in the cache.
     *
     * @return mixed
     */
    public function cache(): mixed
    {
        return Cache::remember(static::cacheKey(), now()->addHour(), function () {
            $domains = LegalDomain::whereNull('archived_at')->get(['id', 'title', 'parent_id'])->keyBy('id');

            $domains = $domains->map(function ($domain) use ($domains) {
                $domain->path = collect();
                $current = $domain->parent_id;

                while ($current) {
                    if ($current = $domains->get($current)) {
                        $domain->path->push($current->title);
                    }

                    $current = $current?->parent_id;
                }

                return $domain->path->reverse()->push($domain->title)->join(' | ');
            })->sort()->toArray();

            $domains[''] = '-';

            return $domains;
        });
    }
}
