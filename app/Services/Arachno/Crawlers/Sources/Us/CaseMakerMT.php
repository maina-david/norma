<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-mt
 * title: USA, CaseMaker Montana
 * url: https://fc7.fastcase.com/outline/MT/482
 */
class CaseMakerMT extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'MT';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-mt',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/MT/482',
                    ]),
                ],
            ],
        ];
    }
}
