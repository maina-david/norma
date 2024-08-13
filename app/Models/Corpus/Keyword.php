<?php

namespace App\Models\Corpus;

use App\Models\AbstractModel;
use App\Models\Traits\UsesUid;
use Exception;

/**
 * @mixin IdeHelperKeyword
 */
class Keyword extends AbstractModel
{
    use UsesUid;

    /*****************************************************************
    / Model Relationships
    /*****************************************************************/

    /*****************************************************************
    / Model Methods
    /*****************************************************************/

    public function getUidString(): string
    {
        return $this->label;
    }

    /**
     * Gets the given keyword, or creates a new one and returns that, if it already exists.
     *
     * @param array<string, mixed> $attributes
     *
     * @throws Exception
     *
     * @return Keyword
     */
    public static function firstOrCreateForUid(array $attributes): Keyword
    {
        if (!isset($attributes['label'])) {
            throw new Exception('The label field is required');
        }

        $uidHash = Keyword::hashUid($attributes['label']);
        /** @var Keyword|null */
        $keyword = Keyword::forUid($uidHash)->first();

        if (!$keyword) {
            $attributes['uid'] = Keyword::hashForDB($attributes['label']);
            /** @var Keyword */
            $keyword = Keyword::create($attributes);
        }

        return $keyword;
    }
}
