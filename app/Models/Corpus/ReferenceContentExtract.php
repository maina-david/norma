<?php

namespace App\Models\Corpus;

use App\Enums\Application\ApplicationType;
use App\Models\AbstractModel;

/**
 * @mixin IdeHelperReferenceContentExtract
 */
class ReferenceContentExtract extends AbstractModel
{
    /**
     * {@inheritDoc}
     */
    public static function excludeFromCrud(): array
    {
        return [
            ApplicationType::admin()->value => [],
            ApplicationType::collaborate()->value => [
                'viewAny', 'view', 'update',
            ],
            ApplicationType::my()->value => [],
        ];
    }
}
