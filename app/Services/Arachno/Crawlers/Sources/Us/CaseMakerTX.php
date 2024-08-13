<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-tx
 * title: USA, Texas Statutes and Administrative Code
 * url: https://fc7.fastcase.com/outline/TX/385
 */
class CaseMakerTX extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'TX';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-tx',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/TX/385',
                    ]),
                ],
            ],
        ];
    }
}
