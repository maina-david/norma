<?php

namespace App\Services\Arachno\Crawlers\Sources\Ca;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQuerySource;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;

/**
 * NB! This is still the old way of doing it... needs to be updated.
 *
 * @codeCoverageIgnore
 */
class CanadaGazetteOld extends AbstractCrawlerConfig
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
            'only_capture_new_links' => true,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    [
                        'url' => 'https://gazette.gc.ca/accueil-home-eng.html',
                    ],
                ],
            ],
            'follow_links_queries' => [
                [
                    'should_run_query' => [],
                    'links_queries' => [
                        [
                            // e.g. https://gazette.gc.ca/accueil-home-eng.html
                            'type' => DomQueryType::CSS,
                            'query' => 'body > main > div:nth-child(3) > div > div > div.col-md-4 > div > ul > li > ul > li:nth-child(1) > a',
                        ],
                        [
                            // e.g. https://gazette.gc.ca/rp-pr/p1/2022/2022-04-09/html/index-eng.html
                            'type' => DomQueryType::XPATH,
                            'query' => '//*[@id="content"]/*[local-name()="h2"]/*[local-name()="a" and text()[contains(.,"Government notices")]]',
                        ],
                    ],
                ],
            ],
            'capture_queries' => [
                [
                    'is_capture_page_query' => [
                        'type' => DomQueryType::REGEX,
                        'source' => DomQuerySource::URL,
                        'query' => '/html\/notice\-avis\-eng\.html/',
                    ],
                    'capture_body_queries' => [
                        [
                            'type' => DomQueryType::CSS,
                            'query' => '#content',
                        ],
                    ],
                    'capture_head_queries' => [
                        [
                            'type' => DomQueryType::CSS,
                            'query' => 'head > title',
                        ],
                        [
                            'type' => DomQueryType::CSS,
                            'query' => 'head > meta[name="dcterms.modified"]',
                        ],
                        [
                            'type' => DomQueryType::XPATH,
                            'query' => '//head//link[@rel="stylesheet" and contains(@href, "theme.min.css")]',
                        ],
                        [
                            'type' => DomQueryType::XPATH,
                            'query' => '//head//link[@rel="stylesheet" and contains(@href, "gazette.css")]',
                        ],
                    ],
                ],
            ],
            'meta_for_update_query' => [
                'type' => DomQueryType::FUNC,
                'query' => fn () => true,
            ],
        ];
    }
}
