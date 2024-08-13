<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-fl
 * title: USA, CaseMaker Florida
 * url: https://fc7.fastcase.com/outline/FL/51
 */
class CaseMakerFL extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'FL';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-fl',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/FL/51',
                    ]),
                ],
            ],
        ];
    }
}
