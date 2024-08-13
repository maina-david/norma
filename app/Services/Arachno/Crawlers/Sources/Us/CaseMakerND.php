<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-nd
 * title: USA, CaseMaker North Dakota
 * url: https://fc7.fastcase.com/outline/ND/483
 */
class CaseMakerND extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'ND';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-nd',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/ND/483',
                    ]),
                ],
            ],
        ];
    }
}
