<?php

namespace App\Enums\Storage\My;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static FolderType global()
 * @method static FolderType organisation()
 * @method static FolderType libryo()
 * @method static FolderType system()
 */
class FolderType extends Enum
{
    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string, int>
     */
    protected static function enums(): array
    {
        return [
            'global' => 1,
            'organisation' => 2,
            'libryo' => 4,
            'system' => 5,
        ];
    }
}
