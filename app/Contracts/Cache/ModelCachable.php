<?php

namespace App\Contracts\Cache;

interface ModelCachable
{
    /**
     * Get the stored results from the cache.
     *
     * @return mixed
     */
    public function get(): mixed;

    /**
     * Store the results in the cache.
     *
     * @return mixed
     */
    public function cache(): mixed;

    /**
     * Flush the cache.
     *
     * @return void
     */
    public function flush(): void;
}
