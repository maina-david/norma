<?php

namespace App\Enums\Auth;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static UserType staff()
 * @method static UserType customer()
 * @method static UserType partner()
 * @method static UserType demo()
 * @method static UserType free()
 * @method static UserType collaborate()
 * @method static UserType other()
 */
class UserType extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'auth.user.user_type.';

    /**
     * Get the allowed enums.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'staff' => 1,
            'customer' => 2,
            'partner' => 3,
            'demo' => 4,
            'free' => 5,
            'collaborate' => 6,
            'other' => 10,
        ];
    }
}
