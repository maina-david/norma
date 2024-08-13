<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-nj
 * title: USA, CaseMaker New Jersey
 * url: https://fc7.fastcase.com/outline/NJ/285
 */
class CaseMakerNJ extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'NJ';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-nj',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/NJ/285',
                    ]),
                ],
            ],
        ];
    }
}
