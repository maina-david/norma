<?php

namespace App\Enums\Assess;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static AssessActivityType answerChange()
 * @method static AssessActivityType fileUpload()
 * @method static AssessActivityType comment()
 * @method static AssessActivityType responseAdded()
 * @method static AssessActivityType requirementLinked()
 * @method static AssessActivityType requirementUnlinked()
 */
class AssessActivityType extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'assess.assessment_item_response.activity.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'answerChange' => 1,
            'fileUpload' => 2,
            'comment' => 3,
            'responseAdded' => 4,
            'requirementLinked' => 5,
            'requirementUnlinked' => 6,
        ];
    }
}
