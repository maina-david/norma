<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-sd
 * title: USA, CaseMaker South Dakota
 * url: https://fc7.fastcase.com/outline/SD/492
 */
class CaseMakerSD extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'SD';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-sd',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/SD/492',
                    ]),
                ],
            ],
        ];
    }
}
