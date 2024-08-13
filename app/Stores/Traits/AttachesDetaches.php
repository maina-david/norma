<?php

namespace App\Stores\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Attaches and detaches relations.
 */
trait AttachesDetaches
{
    /**
     * Attaches the items to the given relation.
     *
     * @param Model                 $model
     * @param string                $relation
     * @param Collection<Model|int> $collection ,
     * @param array<string, mixed>  $pivot      ,
     * @param callable|null         $callback
     *
     * @return void
     */
    public function attachRelations(
        Model $model,
        string $relation,
        Collection $collection,
        array $pivot = [],
        ?callable $callback = null
    ): void {
        foreach ($collection as $item) {
            try {
                $model->{$relation}()->attach($item, $pivot);

                if ($callback) {
                    call_user_func($callback, $item);
                }
            } catch (Exception $e) {
                if ($e->getCode() !== '23000') {
                    // @codeCoverageIgnoreStart
                    throw $e;
                    // @codeCoverageIgnoreEnd
                }
                // was already attached, maybe just update pivot
                // if (!empty($pivot)) {
                //    $model->{$relation}()->updateExistingPivot($item, $pivot);
                // }
            }
        }
    }

    /**
     * Attaches the items to the given relation.
     * Items can either be a list of models or ids.
     *
     * @param Model                 $model
     * @param string                $relation
     * @param Collection<Model|int> $collection ,
     * @param callable|null         $callback
     *
     * @return void
     */
    public function detachRelations(
        Model $model,
        string $relation,
        Collection $collection,
        ?callable $callback = null
    ): void {
        foreach ($collection as $item) {
            $model->{$relation}()->detach($item);

            if ($callback) {
                // @codeCoverageIgnoreStart
                call_user_func($callback, $item);
                // @codeCoverageIgnoreEnd
            }
        }
    }
}
