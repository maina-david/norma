<?php

namespace App\Enums\System;

use App\Enums\Enum;

/**
 * @property string $value
 *
 * @method static self queued()
 * @method static self failed()
 * @method static self finished()
 * @method static self executing()
 */
class JobStatusType extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'system.processing_job.job_type.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string>
     */
    protected static function enums(): array
    {
        return [
            'queued',
            'failed',
            'finished',
            'executing',
        ];
    }
}
