<?php

namespace App\Services\Arachno\Crawlers\Sources\Za;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\Crawl;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Geonames\Location;
use App\Services\Arachno\Frontier\Fetch;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Stores\Arachno\LinkStore;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore
 *
 * The Sabinet API is used for this.
 * We fetch the latest 150 government and provincial gazettes, ordered by publication date descending.
 * The first end point contains most meta data, then the second one is to fetch a download link from cloudfront CDN.
 * As it's a signed download link that expires, we need to download it straight away and add it to the cached_contents table
 * so when it is crawled, it will fetch from cache rather than from the original link, as that might have expired by the time the
 * crawl for the PDF download runs.
 */
class SabinetGazettes extends AbstractSouthAfricaSabinet
{
    /** @var array<string,int> */
    private array $locationsMap = [];

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/api\.sabinet-arq\.com\/search\/resourcespublic/')) {
            $this->handleIndex($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/api\.sabinet-arq\.com\/documents\/[0-9]+\/path$/')) {
            $this->handleDownloadLink($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/cloudfront\.net\//')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/api\.sabinet\-arq\.com/')) {
            $settings = [
                'options' => [
                    'headers' => $this->getHttpHeaders(),
                ],
            ];

            $url = $pageCrawl->pageUrl->url;
            if (str_contains($url, '/search/resourcespublic')) {
                $settings = $this->getDefaultPostData();

                if (str_contains($url, '?provincial')) {
                    $settings['options']['json']['query']['bool']['must'][0]['term']['product_types.keyword'] = 'Legal - Provincial Gazettes';
                    $settings['options']['json']['query']['bool']['must_not'][0]['term']['resource_type.keyword'] = 'Full Provincial Gazette';
                }
                //                if (str_contains($url, '?municipal')) {
                //                    $settings['options']['json']['query']['bool']['must'][0]['term']['product_types.keyword'] = 'Legal - Municipal By‑laws';
                //                    $settings['options']['json']['query']['bool']['must_not'][0]['term']['resource_type.keyword'] = 'Full Provincial Gazette';
                //                }
            }

            $pageCrawl->setHttpSettings($settings);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDefaultPostData(): array
    {
        return [
            'method' => 'POST',
            'options' => [
                'headers' => $this->getHttpHeaders(),
                'json' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'term' => [
                                        'product_types.keyword' => 'Legal - Government Gazettes',
                                    ],
                                ],
                            ],
                            'must_not' => [
                                [
                                    'term' => [
                                        'resource_type.keyword' => 'Full Government Gazette',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'from' => 0,
                    'size' => 250,
                    'sort' => [
                        0 => [
                            'date' => 'desc',
                        ],
                    ],
                    '_source' => [
                        'includes' => [
                            0 => '*',
                        ],
                        'excludes' => ['arq_label', 'discover_status', 'modified_by_sabinet', 'index_letter', 'resource_type_ref', 'field8', 'date_of_file', 'fulltext', 'ocr', 'ocr_text'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleIndex(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $arr = $json['hits']['hits'] ?? [];
        $seenIds = [];
        foreach ($arr as $item) {
            $item = $item['_source'];
            $provincial = false;
            if (isset($seenIds[$item['id']])) {
                continue;
            }
            // it seems sometimes duplicate ID's are returned.
            $seenIds[$item['id']] = true;
            if (isset($item['province'])) {
                $provincial = true;
            }

            /** @var array<string> $keywords */
            $keywords = [];
            $docMeta = new DocMetaDto();
            $docMeta->source_unique_id = 'updates_' . $item['id'];
            $docMeta->language_code = 'eng';
            $docMeta->title = preg_replace("/\s+/", ' ', $item['title']);
            $docMeta->work_type = 'notice';
            try {
                $date = Carbon::createFromFormat('Y-m-d', $item['original_publication_date']);
                if ($date) {
                    $docMeta->work_date = $date;
                }
            } catch (InvalidFormatException $th) {
            }
            $docMeta->work_number = $item['notice_number'] ?? null;
            $docMeta->publication_number = $item['gazette_number'] ?? null;
            $docMeta->publication_document_number = $item['notice_number'] ?? null;
            $docMeta->source_url = 'https://discover.sabinet.co.za/document/' . $item['id'];
            foreach ($item['related_in_terms_of_act'] ?? [] as $related) {
                /** @var string $related */
                $keywords[] = $related;
            }
            foreach ($item['subject_code'] ?? [] as $subject) {
                /** @var string $subject */
                $keywords[] = $subject;
            }
            $jurisdiction = '';
            foreach ($item['originator'] ?? [] as $orig) {
                /** @var string $orig */
                $keywords[] = $orig;
                $jurisdiction = $orig;
            }

            $docMeta->keywords = !empty($keywords) ? $keywords : null;
            $docMeta->primary_location = '4';
            /** @var string $jurisdiction */
            if ($provincial) {
                $province = $item['province'][0] ?? '';
                if (isset($this->locationsMap[$jurisdiction])) {
                    $docMeta->primary_location = (string) $this->locationsMap[$jurisdiction];
                } else {
                    /** @var Location|null */
                    $location = Location::where('location_country_id', 4)
                        ->where('title', $jurisdiction)
                        ->first();

                    if ($location) {
                        $this->locationsMap[$jurisdiction] = $location->id;
                    } else {
                        $this->locationsMap[$jurisdiction] = match ($province) {
                            'Eastern Cape' => 41,
                            'Free State' => 42,
                            'Gauteng' => 43,
                            'KwaZulu-Natal' => 44,
                            'Limpopo' => 45,
                            'Mpumalanga' => 46,
                            'North West' => 47,
                            'Northern Cape' => 48,
                            'Western Cape' => 50,
                            default => 4,
                        };
                    }
                    $docMeta->primary_location = (string) $this->locationsMap[$jurisdiction];
                }
            }
            $url = 'https://api.sabinet-arq.com/documents/' . $item['id'] . '/path';
            $l = new UrlFrontierLink([
                'url' => $url,
                'http_settings' => [
                    'options' => [
                        'headers' => $this->getHttpHeaders(),
                    ],
                ],
            ]);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleDownloadLink(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $link = $json['data']['path'] ?? null;
        if (!$link) {
            return;
        }
        // the link is a signed link that expires, so we need to download it straight away and then store it in the cache
        $response = Http::get($link);
        $url = Str::before($link, '?');
        /** @var Crawl $crawl */
        $crawl = $pageCrawl->pageUrl->crawl;
        app(Fetch::class)->storeToCache($url, $response->headers(), $response->body(), $crawl);
        // store the documents/{id}/path url to links, so we don't crawl it again
        app(LinkStore::class)->firstOrCreateFromUri($pageCrawl->pageUrl->url);

        $this->arachno->followLink($pageCrawl, new UrlFrontierLink([
            'url' => $url,
            'http_settings' => [
                'options' => [
                    'headers' => $this->getHttpHeaders(),
                ],
            ],
        ]), true);
    }

    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'za-sabinet-gazettes',
            'throttle_requests' => 500,
            'start_urls' => $this->getSabinetStartUrls(),
        ];
    }

    /**
     * @return array<string,array<UrlFrontierLink>>
     */
    public function getSabinetStartUrls(): array
    {
        $searchApi = 'https://api.sabinet-arq.com/search/resourcespublic';

        return ['type_' . CrawlType::FOR_UPDATES->value => [
            new UrlFrontierLink(['url' => $searchApi]),
            new UrlFrontierLink([
                'url' => $searchApi . '?provincial', // adding the provincial should make it unique, but not affect anything
            ]),
            //            new UrlFrontierLink([
            //                'url' => $searchApi . '?municipal', // adding the municipal should make it unique, but not affect anything
            //            ]),
        ]];
    }
}
