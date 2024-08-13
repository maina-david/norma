<?php

namespace App\Enums\Storage;

use App\Enums\Enum;

/**
 * @property string $value;
 *
 * @method static FileType collaborators()
 * @method static FileType tasks()
 * @method static FileType my()
 * @method static FileType myAttachment()
 * @method static FileType content()
 */
class FileType extends Enum
{
    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<int, string>
     */
    protected static function enums(): array
    {
        return [
            'collaborators', 'tasks', 'my', 'myAttachment', 'content',
        ];
    }
}
