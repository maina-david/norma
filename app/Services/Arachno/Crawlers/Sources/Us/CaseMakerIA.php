<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-ia
 * title: USA, CaseMaker Iowa
 * url: https://fc7.fastcase.com/outline/IA/455
 */
class CaseMakerIA extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'IA';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-ia',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/IA/455',
                    ]),
                ],
            ],
        ];
    }
}
