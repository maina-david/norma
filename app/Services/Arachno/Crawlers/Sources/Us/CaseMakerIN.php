<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-in
 * title: USA, CaseMaker Indiana
 * url: https://fc7.fastcase.com/outline/IN/454
 */
class CaseMakerIN extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'IN';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-in',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/IN/454',
                    ]),
                ],
            ],
        ];
    }
}
