<?php

namespace App\Enums\Workflows;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static TaskComplexity level1()
 * @method static TaskComplexity level2()
 * @method static TaskComplexity level3()
 * @method static TaskComplexity level4()
 * @method static TaskComplexity level5()
 * @method static TaskComplexity level6()
 * @method static TaskComplexity level200()
 * @method static TaskComplexity level201()
 * @method static TaskComplexity level202()
 * @method static TaskComplexity level203()
 * @method static TaskComplexity level204()
 */
class TaskComplexity extends Enum
{
    protected static string $langPrefix = 'workflows.task.task_complexity.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'level1' => 7,
            'level2' => 8,
            'level3' => 9,
            'level4' => 10,
            'level5' => 15,
            'level6' => 20,

            'level200' => 200,
            'level201' => 201,
            'level202' => 202,
            'level203' => 203,
            'level204' => 204,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function forSelector(bool $withDefault = false): array
    {
        $levels = [
            self::level1()->value => '1',
            self::level2()->value => '2',
            self::level3()->value => '3',
            self::level4()->value => '4',
            self::level5()->value => '5',
            self::level6()->value => '6',

            self::level200()->value => self::level200()->value,
            self::level201()->value => self::level201()->value,
            self::level202()->value => self::level202()->value,
            self::level203()->value => self::level203()->value,
            self::level204()->value => self::level204()->value,
        ];

        $out = [];

        if ($withDefault) {
            $out[] = ['value' => '--disabled--', 'label' => ''];
        }

        foreach (static::lang() as $key => $value) {
            $out[] = ['value' => $levels[$key], 'label' => $value];
        }

        return $out;
    }

    /**
     * Convert the complexity to hours.
     *
     * @return float
     */
    public function hours(): float
    {
        $options = [
            self::level1()->value => 7,
            self::level2()->value => 8,
            self::level3()->value => 9,
            self::level4()->value => 10,
            self::level5()->value => 15,
            self::level6()->value => 20,
        ];

        return isset($options[$this->value]) ? $options[$this->value] / 60 : 0;
    }
}
