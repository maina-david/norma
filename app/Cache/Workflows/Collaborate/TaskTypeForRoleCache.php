<?php

namespace App\Cache\Workflows\Collaborate;

use App\Cache\Abstracts\ModelCache;
use App\Models\Workflows\TaskType;
use Illuminate\Support\Facades\Cache;

class TaskTypeForRoleCache extends ModelCache
{
    /**
     * Get the cache key for the given cache store.
     *
     * @return string
     */
    protected static function cacheKey(): string
    {
        return 'task_type_for_role_cache';
    }

    /**
     * Store the results in the cache.
     *
     * @return mixed
     */
    public function cache(): mixed
    {
        Cache::forget(static::cacheKey());

        return Cache::rememberForever(static::cacheKey(), function () {
            return TaskType::all(['id'])
                ->map(function ($type) {
                    /** @var TaskType $type */
                    return $type->permission(false);
                })
                ->toArray();
        });
    }
}
