<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-ne
 * title: USA, CaseMaker Nebraska
 * url: https://fc7.fastcase.com/outline/NE/468
 */
class CaseMakerNE extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'NE';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-ne',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/NE/468',
                    ]),
                ],
            ],
        ];
    }
}
