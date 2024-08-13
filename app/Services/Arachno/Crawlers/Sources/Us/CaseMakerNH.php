<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-nh
 * title: USA, CaseMaker New Hampshre
 * url: https://fc7.fastcase.com/outline/NH/471
 */
class CaseMakerNH extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'NH';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-nh',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/NH/471',
                    ]),
                ],
            ],
        ];
    }
}
