<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;

/**
 * @codeCoverageIgnore
 * slug: us-casemaker-ma
 * title: USA, Massachusetts Admin Departments and Statutes
 * url: https://fc7.fastcase.com/outline/MA/459
 */
class CaseMakerMA extends AbstractCaseMakerConfig
{
    protected string $jurisdictionCode = 'MA';

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-casemaker-ma',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fc7.fastcase.com/outline/MA/66?docUid=592616388',
                    ]),
                ],
            ],
        ];
    }
}
