<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-wi
 * title: USA, Wisconsin Statutes
 * url: https://fc7.fastcase.com/outline/WI/476?docUid=571756153
 */
class CaseMakerWI extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'WI';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-wi',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/WI/53?docUid=587498157',
                    ]),
                ],
            ],
        ];
    }
}
