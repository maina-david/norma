<?php

namespace App\Http\Controllers\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * getModel fetches a model for the given morphs relation.
 * We don't want to use legacy morph names in URL's so the getLegacyName method converts the URL
 * to the morph name.
 */
trait GetsModelForMorph
{
    /**
     * @param string $type
     * @param int    $id
     * @param string $morphable
     *
     * @return Model
     */
    private function getModel(string $type, int $id, string $morphable): Model
    {
        $type = $this->getLegacyName($type, $morphable);

        $class = Relation::morphMap()[$type] ?? null;
        if (!$class) {
            // @codeCoverageIgnoreStart
            abort(404);
            // @codeCoverageIgnoreEnd
        }

        /** @var Model */
        return $class::findOrFail($id);
    }

    /**
     * @param string $type
     * @param string $morphable
     *
     * @return string
     */
    protected function getLegacyName(string $type, string $morphable): string
    {
        $legacyNames = config('morphs.' . $morphable . '.url_to_morph');
        $oldType = $legacyNames[$type] ?? $type;

        return $oldType;
    }

    /**
     * @param string $type
     * @param string $morphable
     *
     * @return string
     */
    protected function getSlugFromLegacyName(string $type, string $morphable): string
    {
        $legacyNames = config('morphs.' . $morphable . '.url_to_morph');
        if (!$legacyNames) {
            // @codeCoverageIgnoreStart
            throw new Exception('The given morphable does not exist. Check the morphs config file.');
            // @codeCoverageIgnoreEnd
        }
        $oldType = array_flip($legacyNames)[$type] ?? $type;

        return $oldType;
    }
}
