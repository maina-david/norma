<?php

namespace App\Enums\Customer;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static OrganisationType smb()
 * @method static OrganisationType enterprise()
 */
class OrganisationType extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'customer.organisation.organisation_type.';

    /**
     * Get the allowed enums.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'smb' => 1,
            'enterprise' => 2,
        ];
    }
}
