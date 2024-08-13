<?php

namespace App\Enums\Customer;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static OrganisationPlan paid()
 * @method static OrganisationPlan free()
 */
class OrganisationPlan extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'customer.organisation.organisation_plan.';

    /**
     * Get the allowed enums.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'paid' => 1,
            'free' => 2,
        ];
    }
}
