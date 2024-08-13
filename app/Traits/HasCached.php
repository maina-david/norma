<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @mixin Model
 */
trait HasCached
{
    /**
     * Fetch the model from the cache or database.
     *
     * @param int|string|null $modelId
     *
     * @return static|null
     */
    public static function cached(int|string|null $modelId): ?static
    {
        if (!$modelId) {
            return null;
        }

        $key = sprintf('%s_%s', app(static::class)->getTable(), $modelId);

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        /** @var static|null $model */
        $model = static::find($modelId);
        if ($model) {
            Cache::put($key, $model, now()->addMinutes(5));
        }

        return $model;
    }
}
