<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-me
 * title: USA, CaseMaker Maine
 * url: https://fc7.fastcase.com/outline/ME/480
 */
class CaseMakerME extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'ME';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-me',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/ME/480',
                    ]),
                ],
            ],
        ];
    }
}
