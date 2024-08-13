<?php

namespace App\Enums\Compilation;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static self industryAndProductContext()
 * @method static self airQualityFactors()
 * @method static self biodiversityFactors()
 * @method static self hazardousSubstancesFactors()
 * @method static self wasteFactors()
 * @method static self waterFactors()
 * @method static self locationAndSiteContext()
 * @method static self employeeContext()
 * @method static self machineryAndEquipmentFactors()
 * @method static self foodSafety()
 */
class ContextQuestionCategory extends Enum
{
    protected static string $langPrefix = 'compilation.context_question.categories.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string, int>
     */
    protected static function enums(): array
    {
        return [
            'industryAndProductContext' => 1,
            'airQualityFactors' => 2,
            'biodiversityFactors' => 3,
            'hazardousSubstancesFactors' => 4,
            'wasteFactors' => 5,
            'waterFactors' => 6,
            'locationAndSiteContext' => 7,
            'employeeContext' => 9,
            'machineryAndEquipmentFactors' => 10,
            'foodSafety' => 11,
        ];
    }
}
