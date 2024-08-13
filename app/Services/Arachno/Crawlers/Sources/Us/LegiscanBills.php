<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQuerySource;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\LinkToUri;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Services\Arachno\Parse\DomQuery;
use App\Services\Arachno\Support\ExtractTableLinks;
use DOMElement;
use Illuminate\Support\Str;

/**
 * NB! This is still the old way of doing it... needs to be updated.
 *
 * @codeCoverageIgnore
 * slug: us-legiscan-bills
 * title: Legiscan Bills
 * url: https://legiscan.com/.
 */
class LegiscanBills extends AbstractCrawlerConfig
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
            'slug' => 'us-legiscan-bills',
            'only_capture_new_links' => true,
            'throttle_requests' => 3500, // see https://legiscan.com/robots.txt   Crawl-delay: 2 - local IP got blocked, so going to be careful here
            'start_urls' => $this->getLegiscanStartUrls(),
            'follow_links_queries' => [
                [
                    'should_run_query' => [
                        'type' => DomQueryType::REGEX,
                        'query' => '/legiscan.com\/[A-Z]{2}\/legislation/',
                        'source' => DomQuerySource::URL,
                    ],
                    'links_queries' => [
                        [
                            'type' => DomQueryType::FUNC,
                            // 'query' => 'table#gaits-browser tbody tr td:nth-child(2) a',
                            'query' => function (PageCrawl $pageCrawl) {
                                // @codeCoverageIgnoreStart
                                return app(ExtractTableLinks::class)->extract(
                                    $pageCrawl,
                                    new DomQuery(['type' => DomQueryType::CSS, 'query' => 'table#gaits-browser tbody tr']),
                                    new DomQuery(['type' => DomQueryType::CSS, 'query' => 'td:nth-child(2) a']),
                                    [
                                        'work_number' => new DomQuery(['type' => DomQueryType::CSS, 'query' => 'td:nth-child(2) a']),
                                        'work_date' => new DomQuery(['type' => DomQueryType::CSS, 'query' => 'td:nth-child(5) .gaits-browse-date']),
                                    ]
                                );
                                // @codeCoverageIgnoreEnd
                            },
                        ],
                    ],
                ],
                [
                    'should_run_query' => [
                        'type' => DomQueryType::REGEX,
                        'query' => '/legiscan\.com\/[A-Z]{2}\/bill\//',
                        'source' => DomQuerySource::URL,
                    ],
                    'links_queries' => [
                        [
                            'type' => DomQueryType::FUNC,
                            'query' => function (PageCrawl $pageCrawl) {
                                // @codeCoverageIgnoreStart
                                $docMeta = new DocMetaDto();
                                // e.g. https://legiscan.com/CA/bill/AB2287/2021
                                $uniqueId = str_replace(['/bill', 'https://legiscan.com'], '', $pageCrawl->getFinalRedirectedUrl()); // e.g. /CA/AB2287/2021
                                $workNumber = Str::afterLast(Str::beforeLast($uniqueId, '/'), '/'); // e.g. AB2287
                                $title = $pageCrawl->domCrawler->filter('#gaits-header h1.title')->getNode(0)?->textContent ?? '';
                                $docMeta->title = $title . ' - ' . ($pageCrawl->domCrawler->filter('#content-area #bill-title')->getNode(0)?->textContent ?? '');
                                $docMeta->source_unique_id = $uniqueId;
                                $docMeta->work_number = $workNumber;
                                $docMeta->summary = $pageCrawl->domCrawler->filter('#content-area #bill-summary')->getNode(0)?->textContent ?? null;
                                preg_match('/^[HSA]{1}([A-Z]{1,2})/', $workNumber, $matches);
                                $workTypeInitials = $matches[1] ?? null;
                                $docMeta->work_type = match ($workTypeInitials) {
                                    'B', 'LD', 'RB', 'F' => 'bill',
                                    'R', 'JR', 'CR', 'J', 'CER', 'PR' => 'resolution',
                                    'M', 'MR', 'CMR', 'JM' => 'memorial',
                                    'CA', 'JRCA', 'CACR' => 'amendment',
                                    'P' => 'proclamation',
                                    'EO' => 'executive_order',
                                    'CSR' => 'circular',
                                    'A' => 'notice',
                                    default => 'bill',
                                };
                                $jurisdiction = (string) Str::of($uniqueId)->match('/^\/([A-Z]{2})\//');
                                $docMeta->primary_location = $this->getLegiscanJurisdictions()[$jurisdiction]['location_id'] ?? null;

                                /** @var DOMElement|null */
                                $textLink = $pageCrawl->domCrawler->filter('#bill-last-action a[href*="/text/"]')->getNode(0);
                                if (is_null($textLink)) {
                                    return [];
                                }
                                $uri = app(LinkToUri::class)->convert($textLink->getAttribute('href'), $pageCrawl->getFinalRedirectedUrl());

                                $l = new UrlFrontierLink(['url' => (string) $uri, 'anchor_text' => 'Texts']);
                                $l->_metaDto = $docMeta;

                                return [$l];
                                // @codeCoverageIgnoreEnd
                            },
                        ],
                    ],
                ],
                [
                    'should_run_query' => [
                        'type' => DomQueryType::REGEX,
                        'query' => '/legiscan\.com\/[A-Z]{2}\/text\//',
                        'source' => DomQuerySource::URL,
                    ],
                    'links_queries' => [
                        [
                            'type' => DomQueryType::XPATH,
                            'query' => '//*[@id="content-area"]//*[@id="gaits-wrapper"]//a[contains(@href, "/text/") and (contains(@href, ".html") or contains(@href, ".pdf"))]',
                        ],
                    ],
                ],
            ],
            'is_meta_page_query' => [
                // all meta data is extracted from referer links
                'type' => DomQueryType::REGEX,
                'query' => '/legiscan\.com\/[A-Z]{2}\/text\/.*\.html$/',
                'source' => DomQuerySource::URL,
            ],
            'capture_queries' => [
                [
                    'is_capture_page_query' => [
                        'type' => DomQueryType::REGEX,
                        'query' => '/legiscan\.com\/[A-Z]{2}\/text\/.*\.html$/',
                        'source' => DomQuerySource::URL,
                    ],
                    'capture_body_queries' => [
                        [
                            'type' => DomQueryType::CSS,
                            'query' => '#bill_all',
                        ],
                    ],
                    'capture_head_queries' => [
                        [
                            'type' => DomQueryType::CSS,
                            'query' => 'head > title',
                        ],
                    ],
                ],
            ],
            'meta_for_update_query' => [
                'type' => DomQueryType::FUNC,
                'query' => fn () => true,
            ],
            'meta_language_code_query' => [
                'type' => DomQueryType::TEXT,
                'query' => 'eng',
            ],
        ];
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string, array<int, array<mixed>>>
     */
    private function getLegiscanStartUrls(): array
    {
        $arr = $this->getLegiscanJurisdictions();
        $out = [];
        foreach ($arr as $url) {
            $out[] = ['url' => $url['url']];
        }

        return ['type_' . CrawlType::FOR_UPDATES->value => $out];
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string, array<string,mixed>>
     */
    private function getLegiscanJurisdictions(): array
    {
        return [
            // 'AK' => ['url' => 'https://legiscan.com/AK/legislation?status=passed', 'location_id' => '2335'],
            // 'AL' => ['url' => 'https://legiscan.com/AL/legislation?status=passed', 'location_id' => '2334'],
            'AR' => ['url' => 'https://legiscan.com/AR/legislation?status=passed', 'location_id' => '2337'],
            'AZ' => ['url' => 'https://legiscan.com/AZ/legislation?status=passed', 'location_id' => '2336'],
            'CA' => ['url' => 'https://legiscan.com/CA/legislation?status=passed', 'location_id' => '2338'],
            'CO' => ['url' => 'https://legiscan.com/CO/legislation?status=passed', 'location_id' => '2339'],
            'CT' => ['url' => 'https://legiscan.com/CT/legislation?status=passed', 'location_id' => '2340'],
            // 'DE' => ['url' => 'https://legiscan.com/DE/legislation?status=passed', 'location_id' => '2341'],
            'FL' => ['url' => 'https://legiscan.com/FL/legislation?status=passed', 'location_id' => '2343'],
            'GA' => ['url' => 'https://legiscan.com/GA/legislation?status=passed', 'location_id' => '2344'],
            'HI' => ['url' => 'https://legiscan.com/HI/legislation?status=passed', 'location_id' => '2345'],
            'IA' => ['url' => 'https://legiscan.com/IA/legislation?status=passed', 'location_id' => '2349'],
            'ID' => ['url' => 'https://legiscan.com/ID/legislation?status=passed', 'location_id' => '2346'],
            'IL' => ['url' => 'https://legiscan.com/IL/legislation?status=passed', 'location_id' => '2347'],
            'IN' => ['url' => 'https://legiscan.com/IN/legislation?status=passed', 'location_id' => '2348'],
            // 'KS' => ['url' => 'https://legiscan.com/KS/legislation?status=passed', 'location_id' => '2350'],
            'KY' => ['url' => 'https://legiscan.com/KY/legislation?status=passed', 'location_id' => '2351'],
            'LA' => ['url' => 'https://legiscan.com/LA/legislation?status=passed', 'location_id' => '2352'],
            'MA' => ['url' => 'https://legiscan.com/MA/legislation?status=passed', 'location_id' => '2355'],
            'MD' => ['url' => 'https://legiscan.com/MD/legislation?status=passed', 'location_id' => '2354'],
            // 'ME' => ['url' => 'https://legiscan.com/ME/legislation?status=passed', 'location_id' => '2353'],
            'MI' => ['url' => 'https://legiscan.com/MI/legislation?status=passed', 'location_id' => '2356'],
            'MN' => ['url' => 'https://legiscan.com/MN/legislation?status=passed', 'location_id' => '2357'],
            'MO' => ['url' => 'https://legiscan.com/MO/legislation?status=passed', 'location_id' => '2359'],
            // 'MS' => ['url' => 'https://legiscan.com/MS/legislation?status=passed', 'location_id' => '2358'],
            // 'MT' => ['url' => 'https://legiscan.com/MT/legislation?status=passed', 'location_id' => '2360'],
            // 'NC' => ['url' => 'https://legiscan.com/NC/legislation?status=passed', 'location_id' => '2367'],
            // 'ND' => ['url' => 'https://legiscan.com/ND/legislation?status=passed', 'location_id' => '2368'],
            // 'NE' => ['url' => 'https://legiscan.com/NE/legislation?status=passed', 'location_id' => '2361'],
            // 'NH' => ['url' => 'https://legiscan.com/NH/legislation?status=passed', 'location_id' => '2363'],
            'NJ' => ['url' => 'https://legiscan.com/NJ/legislation?status=passed', 'location_id' => '2364'],
            // 'NM' => ['url' => 'https://legiscan.com/NM/legislation?status=passed', 'location_id' => '2365'],
            'NV' => ['url' => 'https://legiscan.com/NV/legislation?status=passed', 'location_id' => '2362'],
            'NY' => ['url' => 'https://legiscan.com/NY/legislation?status=passed', 'location_id' => '2366'],
            'OH' => ['url' => 'https://legiscan.com/OH/legislation?status=passed', 'location_id' => '2369'],
            // 'OK' => ['url' => 'https://legiscan.com/OK/legislation?status=passed', 'location_id' => '2370'],
            'OR' => ['url' => 'https://legiscan.com/OR/legislation?status=passed', 'location_id' => '2371'],
            'PA' => ['url' => 'https://legiscan.com/PA/legislation?status=passed', 'location_id' => '2372'],
            // 'RI' => ['url' => 'https://legiscan.com/RI/legislation?status=passed', 'location_id' => '2373'],
            'SC' => ['url' => 'https://legiscan.com/SC/legislation?status=passed', 'location_id' => '2374'],
            // 'SD' => ['url' => 'https://legiscan.com/SD/legislation?status=passed', 'location_id' => '2375'],
            'TN' => ['url' => 'https://legiscan.com/TN/legislation?status=passed', 'location_id' => '2376'],
            'TX' => ['url' => 'https://legiscan.com/TX/legislation?status=passed', 'location_id' => '2377'],
            'UT' => ['url' => 'https://legiscan.com/UT/legislation?status=passed', 'location_id' => '2378'],
            'VA' => ['url' => 'https://legiscan.com/VA/legislation?status=passed', 'location_id' => '2380'],
            // 'VT' => ['url' => 'https://legiscan.com/VT/legislation?status=passed', 'location_id' => '2379'],
            'WA' => ['url' => 'https://legiscan.com/WA/legislation?status=passed', 'location_id' => '2381'],
            'WI' => ['url' => 'https://legiscan.com/WI/legislation?status=passed', 'location_id' => '2383'],
            // 'WV' => ['url' => 'https://legiscan.com/WV/legislation?status=passed', 'location_id' => '2382'],
            // 'WY' => ['url' => 'https://legiscan.com/WY/legislation?status=passed', 'location_id' => '2384'],
            'DC' => ['url' => 'https://legiscan.com/DC/legislation?status=passed', 'location_id' => '2342'],
            // 'US' => ['url' => 'https://legiscan.com/US/legislation?status=passed', 'location_id' => '384'],
        ];
    }
}
