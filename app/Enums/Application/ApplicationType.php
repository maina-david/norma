<?php

namespace App\Enums\Application;

use App\Enums\Enum;

/**
 * @property string $value;
 *
 * @method static ApplicationType collaborate()
 * @method static ApplicationType admin()
 * @method static ApplicationType my()
 */
class ApplicationType extends Enum
{
    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return string[]
     */
    protected static function enums(): array
    {
        return ['collaborate', 'admin', 'my'];
    }

    /**
     * Get the valid application types for checks.
     *
     * @return string[]
     */
    public static function validTypes(): array
    {
        return self::enums();
    }
}
