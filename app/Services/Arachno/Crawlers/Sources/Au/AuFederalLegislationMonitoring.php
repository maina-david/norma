<?php

namespace App\Services\Arachno\Crawlers\Sources\Au;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQuerySource;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use Symfony\Component\DomCrawler\Crawler;

/**
 * NB! This is still the old way of doing it... needs to be updated.
 *
 * @codeCoverageIgnore
 */
class AuFederalLegislationMonitoring extends AbstractCrawlerConfig
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
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    [
                        'url' => 'https://www.legislation.gov.au/WhatsNew',
                    ],
                ],
            ],
            'follow_links_queries' => [
                [
                    'should_run_query' => [
                        'type' => DomQueryType::REGEX,
                        'query' => '/legislation\.gov\.au\/WhatsNew/',
                        'source' => DomQuerySource::URL,
                    ],

                    'links_queries' => [
                        [
                            'type' => DomQueryType::FUNC,
                            'query' => function (PageCrawl $pageCrawl) {
                                // @codeCoverageIgnoreStart
                                /** @var Crawler $crawler */
                                $crawler = $pageCrawl->domCrawler;

                                /** @var Crawler $gazetteSibling */
                                $gazetteSibling = $crawler
                                    ->filterXPath('//tr[not(@style)]//td//p//strong[text()="Gazette"]')
                                    ->first()
                                    ->closest('tr');

                                $gazetteSibling = $gazetteSibling->nextAll();

                                $nextTitle = $gazetteSibling->filterXPath('//tr[not(@style)]//td//p//strong');

                                // There is no next sibling.
                                if (!$nextTitle->getNode(0)) {
                                    return $gazetteSibling->filterXPath('//*//a[contains(@href, "Details")]');
                                }

                                $nextTitle = $nextTitle->first()->text();
                                $position = collect([]);

                                $gazetteSibling->each(function ($node, $index) use ($position, $nextTitle) {
                                    if (str_contains($node->text(), $nextTitle)) {
                                        $position->push($index);
                                    }
                                });

                                /** @var int|null $index */
                                $index = $position->first();

                                $gazetteSibling = $index
                                    ? $gazetteSibling->reduce(fn ($node, $i) => $i < $index)
                                    : $gazetteSibling;

                                return $gazetteSibling->filterXPath('//*//a[contains(@href, "Details")]');
                                // @codeCoverageIgnoreEnd
                            },
                        ],
                    ],
                ],
                [
                    'should_run_query' => [
                        'type' => DomQueryType::REGEX,
                        'query' => '/legislation\.gov\.au\/Details/',
                        'source' => DomQuerySource::URL,
                    ],

                    'links_queries' => [
                        [
                            'type' => DomQueryType::CSS,
                            'query' => '.headroom.headroom--top .rtsLevel.rtsLevel1 li:nth-child(2) a[href*=Download]',
                        ],
                    ],
                ],

                [
                    'should_run_query' => [
                        'type' => DomQueryType::REGEX,
                        'query' => '/legislation\.gov\.au\/Details\/.+\/Download/',
                        'source' => DomQuerySource::URL,
                    ],

                    'links_queries' => [
                        [
                            'type' => DomQueryType::XPATH,
                            'query' => '//*[contains(@id, "PDF")][@href]',
                        ],
                    ],
                ],
            ],
            'is_meta_page_query' => [
                'type' => DomQueryType::REGEX,
                'query' => '/legislation\.gov\.au\/Details\/.+\/Download/',
                'source' => DomQuerySource::URL,
            ],
            'meta_unique_id_query' => [
                'type' => DomQueryType::REGEX,
                'source' => DomQuerySource::URL,
                'query' => '/www\.legislation.gov.au\/Details\/(.+)\/.+/',
            ],
            'meta_language_code_query' => [
                'type' => DomQueryType::TEXT,
                'query' => 'eng',
            ],
            'meta_title_query' => [
                'type' => DomQueryType::CSS,
                'query' => 'title',
            ],
            'meta_for_update_query' => [
                'type' => DomQueryType::FUNC,
                'query' => fn () => true,
            ],
            'meta_work_type_query' => [
                'type' => DomQueryType::TEXT,
                'query' => 'gazette',
            ],
            'meta_work_number_query' => [
                'type' => DomQueryType::REGEX,
                'source' => DomQuerySource::URL,
                'query' => '/www\.legislation.gov.au\/Details\/(.+)\/.+/',
            ],
            'meta_work_date_query' => [
                'type' => DomQueryType::XPATH,
                'query' => '//meta[@name="DC.Date.Created"]//@content',
            ],
            'meta_source_url_query' => [
                'type' => DomQueryType::XPATH,
                'query' => '//meta[@name="DC.Identifier"]//@content',
            ],
            'meta_download_url_query' => [
                'type' => DomQueryType::FUNC,
                'query' => function (PageCrawl $pageCrawl) {
                    // @codeCoverageIgnoreStart
                    /** @var Crawler $crawler */
                    $crawler = $pageCrawl->domCrawler;

                    return $crawler
                        ->filterXPath('//*[contains(@id, "PDF")][@href]')
                        ->first()
                        ->attr('href');
                    // @codeCoverageIgnoreEnd
                },
            ],
            'meta_primary_location_query' => [
                'type' => DomQueryType::TEXT,
                'query' => '392',
            ],
        ];
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     **/
    public function postProcessCss(string $css): string
    {
        return str_replace('.bootstrap-scope ', '', $css);
    }
}
