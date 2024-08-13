<?php

namespace App\Enums\System;

use App\Enums\Enum;

/**
 * @property int $value
 *
 * @method static self failed()
 * @method static self pending()
 * @method static self processing()
 * @method static self complete()
 */
class ProcessingJobStatus extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'system.processing_job.job_status.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string, int>
     */
    protected static function enums(): array
    {
        return [
            'failed' => -1,
            'pending' => 0,
            'processing' => 1,
            'complete' => 2,
        ];
    }
}
