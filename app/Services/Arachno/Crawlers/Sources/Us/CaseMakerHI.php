<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-hi
 * title: USA, CaseMaker Hawaii
 * url: https://fc7.fastcase.com/outline/HI/445
 */
class CaseMakerHI extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'HI';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-hi',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/HI/445',
                    ]),
                ],
            ],
        ];
    }
}
