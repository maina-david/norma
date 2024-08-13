<?php

namespace App\Enums\Tasks;

use App\Enums\Traits\HasLabels;

enum TaskRepeatInterval: int
{
    use HasLabels;

    case DAY = 1;
    case WEEK = 2;
    case MONTH = 3;
    case YEAR = 4;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'assess.assessment_item.reassess_interval.';
    }

    /**
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getRelative(): string
    {
        return match ($this) {
            self::DAY => 'Daily',
            self::WEEK => 'Weekly',
            self::MONTH => 'Monthly',
            self::YEAR => 'Annually',
        };
    }
}
