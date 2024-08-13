<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-nv
 * title: USA, CaseMaker Nevada
 * url: https://fc7.fastcase.com/outline/NV/472
 */
class CaseMakerNV extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'NV';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-nv',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/NV/472',
                    ]),
                ],
            ],
        ];
    }
}
