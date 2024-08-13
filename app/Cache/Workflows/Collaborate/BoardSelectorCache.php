<?php

namespace App\Cache\Workflows\Collaborate;

use App\Cache\Abstracts\ModelCache;
use App\Models\Workflows\Board;
use Illuminate\Support\Facades\Cache;

class BoardSelectorCache extends ModelCache
{
    /**
     * Get the cache key for the given cache store.
     *
     * @return string
     */
    protected static function cacheKey(): string
    {
        return 'board_selector_cache';
    }

    /**
     * Store the results in the cache.
     *
     * @return mixed
     */
    public function cache(): mixed
    {
        return Cache::remember(static::cacheKey(), now()->addHour(), function () {
            $boards = Board::orderBy('title')->pluck('title', 'id')->toArray();

            $boards['--disabled--'] = '';

            return $boards;
        });
    }
}
