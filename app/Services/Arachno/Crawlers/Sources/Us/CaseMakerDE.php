<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-de
 * title: USA, CaseMaker Delaware
 * url: https://fc7.fastcase.com/outline/DE/464
 */
class CaseMakerDE extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'DE';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-de',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/DE/464',
                    ]),
                ],
            ],
        ];
    }
}
