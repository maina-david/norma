<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-wa
 * title: Casemaker, Washington State
 * url: https://fc7.fastcase.com/outline/WA/474
 */
class CaseMakerWA extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'WA';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-wa',
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/WA/474',
                    ]),
                ],
            ],
        ];
    }
}
