<?php

namespace App\Enums\System;

use App\Enums\Enum;

/**
 * @property int $value
 *
 * @method static self referenceParentsPositions()
 * @method static self fetchSelectors()
 * @method static self cacheHighlights()
 * @method static self arachnoCapture()
 * @method static self arachnoRecapture()
 */
class ProcessingJobType extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'system.processing_job.job_type.';

    /**
     * Get the allowed enums. It can either be keyed or not.
     *
     * @return array<string, string>
     */
    protected static function enums(): array
    {
        return [
            'referenceParentsPositions' => 'reference.parents-positions',
            'fetchSelectors' => 'expressions.fetch-selectors',
            'cacheHighlights' => 'work.cache-highlights',
            'arachnoCapture' => 'arachno.capture',
            'arachnoRecapture' => 'arachno.recapture',
        ];
    }
}
