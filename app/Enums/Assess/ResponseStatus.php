<?php

namespace App\Enums\Assess;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static ResponseStatus notApplicable()
 * @method static ResponseStatus no()
 * @method static ResponseStatus yes()
 * @method static ResponseStatus notAssessed()
 * @method static ResponseStatus draft()
 */
class ResponseStatus extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'assess.assessment_item_response.status.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'yes' => 3,
            'no' => 1,
            'notAssessed' => 4,
            'notApplicable' => 0,
            'draft' => 5,
        ];
    }
}
