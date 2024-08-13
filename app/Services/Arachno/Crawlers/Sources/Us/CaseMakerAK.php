<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-ak
 * title: USA, CaseMaker Delaware
 * url: https://fc7.fastcase.com/outline/AK/473
 */
class CaseMakerAK extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'AK';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-ak',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/AK/473',
                    ]),
                ],
            ],
        ];
    }
}
