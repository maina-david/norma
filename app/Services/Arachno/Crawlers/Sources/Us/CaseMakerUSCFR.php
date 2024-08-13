<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-us-admin
 * title: US, USCFR Administrative Code
 * url: https://fc7.fastcase.com/outline/USCFR
 */
class CaseMakerUSCFR extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'USCFR/US';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-us-admin',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/USCFR',
                    ]),
                ],
            ],
        ];
    }
}
