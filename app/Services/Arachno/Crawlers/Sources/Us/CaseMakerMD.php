<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-md
 * title: USA, CaseMaker Maryland
 * url: https://fc7.fastcase.com/outline/MD/79
 */
class CaseMakerMD extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'MD';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-md',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/MD/79',
                    ]),
                ],
            ],
        ];
    }
}
