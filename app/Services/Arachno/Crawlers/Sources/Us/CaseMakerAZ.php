<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-az
 * title: USA, CaseMaker Arizona
 * url: https://fc7.fastcase.com/outline/AZ/450
 */
class CaseMakerAZ extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'AZ';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-az',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/AZ/450',
                    ]),
                ],
            ],
        ];
    }
}
