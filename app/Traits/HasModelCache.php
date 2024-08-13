<?php

namespace App\Traits;

use App\Contracts\Cache\ModelCachable;

trait HasModelCache
{
    /**
     * Get the cache classes.
     *
     * @return array<int, class-string>
     */
    abstract protected static function modelCaches(): array;

    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function bootHasModelCache(): void
    {
        /* @phpstan-ignore-next-line */
        static::saved(fn () => static::reCacheAll());
        /* @phpstan-ignore-next-line */
        static::deleted(fn () => static::reCacheAll());

        if (method_exists(static::class, 'restored')) {
            /* @phpstan-ignore-next-line */
            static::restored(fn () => static::reCacheAll());
        }
    }

    /**
     * Clear all the caches and cache again.
     *
     * @return void
     */
    public static function reCacheAll(): void
    {
        foreach (static::modelCaches() as $cache) {
            /** @var ModelCachable $cache */
            $cache = new $cache();

            self::reCache($cache);
        }
    }

    /**
     * Clear the cache and cache it again.
     *
     * @param ModelCachable $cashable
     *
     * @return void
     */
    private static function reCache(ModelCachable $cashable): void
    {
        $cashable->flush();
        $cashable->cache();
    }
}
