<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-oh
 * title: USA, Ohio Administrative Code
 * url: https://fc7.fastcase.com/outline/OH/625?docUid=610680866
 */
class CaseMakerOH extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'OH';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-oh',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/OH/625?docUid=610680866',
                    ]),
                ],
            ],
        ];
    }
}
