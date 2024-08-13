<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-cd
 * title: USA, CaseMaker District of Colombia Code
 * url: https://fc7.fastcase.com/outline/CD/102
 */
class CaseMakerCD extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'CD';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-cd',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/CD/102',
                    ]),
                ],
            ],
        ];
    }
}
