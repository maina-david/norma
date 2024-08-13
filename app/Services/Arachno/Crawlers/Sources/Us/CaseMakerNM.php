<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-nm
 * title: USA, CaseMaker New Mexico
 * url: https://fc7.fastcase.com/outline/NM/236
 */
class CaseMakerNM extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'NM';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-nm',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/NM/236',
                    ]),
                ],
            ],
        ];
    }
}
