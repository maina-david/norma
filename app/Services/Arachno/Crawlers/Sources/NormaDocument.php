<?php

namespace App\Services\Arachno\Crawlers\Sources;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQuerySource;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore
 */
class NormaDocument extends AbstractCrawlerConfig
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
                    [
                        'url' => 'https://sites.google.com/norma.com/norma-document-test/home',
                    ],
                ],
            ],
            'throttle_requests' => 50,
            'is_meta_page_query' => [
                'type' => DomQueryType::REGEX,
                'source' => DomQuerySource::URL,
                'query' => '/norma\-document\-test\/home/',
            ],
            'meta_unique_id_query' => [
                'type' => DomQueryType::FUNC,
                'query' => fn () => 'norma-test',
            ],
            'meta_title_query' => [
                'type' => DomQueryType::CSS,
                'query' => 'h1',
            ],
            'capture_queries' => [
                [
                    'is_capture_page_query' => [
                        'type' => DomQueryType::REGEX,
                        'source' => DomQuerySource::URL,
                        'query' => '/norma\-document\-test\/home/',
                    ],
                    'capture_body_queries' => [
                        [
                            'type' => DomQueryType::CSS,
                            'query' => '[role="main"]',
                        ],
                    ],
                    'capture_head_queries' => [
                        [
                            'type' => DomQueryType::XPATH,
                            'query' => '//head//title',
                        ],
                        [
                            'type' => DomQueryType::CSS,
                            'query' => 'head style',
                        ],
                    ],
                    'capture_remove_queries' => [
                        [
                            'type' => DomQueryType::CSS,
                            'query' => '#h.INITIAL_GRID.lsnowq1gvcmc',
                        ],
                        [
                            'type' => DomQueryType::XPATH,
                            'query' => '//*[@id="h.2e91b5bbddfd6474_13"]/div/div',
                        ],
                        [
                            'type' => DomQueryType::CSS,
                            'query' => 'svg',
                        ],
                    ],
                ],
            ],
            'is_toc_page_query' => [
                'type' => DomQueryType::REGEX,
                'source' => DomQuerySource::URL,
                'query' => '/norma\-document\-test\/home/',
            ],
            'toc_query' => [
                'type' => DomQueryType::XPATH,
                'query' => '//*[@id="h.2e91b5bbddfd6474_13"]/div/div',
            ],
            'toc_item_determine_depth' => function ($nodeDto) {
                // @codeCoverageIgnoreStart
                return Str::startsWith($nodeDto['node']->textContent, 'Chapter') ? 1 : 2;
                // @codeCoverageIgnoreEnd
            },
        ];
    }
}
