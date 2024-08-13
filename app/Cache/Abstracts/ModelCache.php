<?php

namespace App\Cache\Abstracts;

use App\Contracts\Cache\ModelCachable;
use Illuminate\Support\Facades\Cache;

abstract class ModelCache implements ModelCachable
{
    /**
     * Get the cache key for the given cache store.
     *
     * @return string
     */
    abstract protected static function cacheKey(): string;

    /**
     * Get the stored results from the cache.
     *
     * @return mixed
     */
    public function get(): mixed
    {
        if (Cache::has(static::cacheKey())) {
            return Cache::get(static::cacheKey());
        }

        return $this->cache();
    }

    /**
     * Flush the cache.
     *
     * @return void
     */
    public function flush(): void
    {
        Cache::forget(static::cacheKey());
    }
}
