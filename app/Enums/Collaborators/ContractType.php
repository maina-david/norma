<?php

namespace App\Enums\Collaborators;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static ContractType standard()
 * @method static ContractType zeroHour()
 */
class ContractType extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'collaborators.contract_type.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'standard' => 1,
            'zeroHour' => 2,
        ];
    }
}
