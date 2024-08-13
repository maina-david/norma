<?php

namespace App\Services\Arachno\Crawlers\Sources;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQuerySource;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;

class TestPdfConfig extends AbstractCrawlerConfig
{
    /**
     * @param PageCrawl $pageCrawl
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
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
            'meta_unique_id_query' => [
                'type' => DomQueryType::REGEX,
                'source' => DomQuerySource::URL,
                'query' => '/.*/',
            ],
            'meta_title_query' => [
                'type' => DomQueryType::PDF,
            ],
            'meta_summary_query' => [
                'type' => DomQueryType::PDF,
            ],
            'throttle_requests' => 50,
        ];
    }
}
