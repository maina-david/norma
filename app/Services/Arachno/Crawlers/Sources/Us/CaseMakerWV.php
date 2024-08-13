<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-wv
 * title: USA, CaseMaker West Virginia
 * url: https://fc7.fastcase.com/outline/WV/82
 */
class CaseMakerWV extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'WV';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-wv',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/WV/82',
                    ]),
                ],
            ],
        ];
    }
}
