<?php

namespace App\Enums\Corpus;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static WorkStatus inactive()
 * @method static WorkStatus active()
 * @method static WorkStatus pending()
 * @method static WorkStatus delete()
 */
class WorkStatus extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'corpus.work.work_status.';

    /**
     * Get the allowed enums.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'inactive' => 0,
            'active' => 1,
            'pending' => 3,
            'delete' => 4,
        ];
    }
}
