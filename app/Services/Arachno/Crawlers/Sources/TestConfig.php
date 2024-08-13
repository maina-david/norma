<?php

namespace App\Services\Arachno\Crawlers\Sources;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;

class TestConfig extends AbstractCrawlerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
    }

    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'start_urls' => [
                'type_' . CrawlType::GENERAL->value => [
                    new UrlFrontierLink([
                        'url' => 'https://example.com',
                    ]),
                ],
            ],
            'throttle_requests' => 50,
        ];
    }
}
