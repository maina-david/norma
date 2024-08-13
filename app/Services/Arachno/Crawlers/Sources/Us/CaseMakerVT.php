<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-vt
 * title: USA, CaseMaker Vermont
 * url: https://fc7.fastcase.com/outline/VT/74
 */
class CaseMakerVT extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'VT';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-vt',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/VT/74',
                    ]),
                ],
            ],
        ];
    }
}
