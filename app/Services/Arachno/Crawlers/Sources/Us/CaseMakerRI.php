<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-ri
 * title: USA, CaseMaker Rhode Island
 * url: https://fc7.fastcase.com/outline/RI/1366
 */
class CaseMakerRI extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'RI';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-ri',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/RI/1366',
                    ]),
                ],
            ],
        ];
    }
}
