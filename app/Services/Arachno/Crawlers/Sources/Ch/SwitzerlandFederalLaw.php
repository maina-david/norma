<?php

namespace App\Services\Arachno\Crawlers\Sources\Ch;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 *
 *  slug: ch-fedlex
 *  title: The federal law publication platform
 *  url: https://www.fedlex.admin.ch/
 */
class SwitzerlandFederalLaw extends AbstractCrawlerConfig
{
    protected string $baseLink = 'https://www.fedlex.admin.ch';

    /**
     * @return array<int, \App\Models\Arachno\UrlFrontierLink>
     */
    protected function getFullCrawlUrl(): array
    {
        $endYear = now()->startOfYear()->year;
        $fullCrawl = [];

        for ($startYear = 1998; $startYear <= $endYear; $startYear++) {
            $fullCrawl[] = new UrlFrontierLink([
                'url' => "https://www.fedlex.admin.ch/elasticsearch/proxy/_search?index=data&oc={$startYear}",
            ]);
        }

        return $fullCrawl;
    }

    /**
     * @return array<\App\Models\Arachno\UrlFrontierLink>
     */
    protected function getInternalUrl(): array
    {
        $internalUrls = [];
        for ($i = 1; $i <= 9; $i++) {
            $internalUrls[] = new UrlFrontierLink([
                'url' => "https://www.fedlex.admin.ch/de/cc/internal-law/{$i}",
            ]);
        }

        return $internalUrls;
    }

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'ch-fedlex',
            'throttle_requests' => 800,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    ...$this->getFullCrawlUrl(),
                    ...$this->getInternalUrl(),
                ],
                'type_' . CrawlType::WORK_CHANGES_DISCOVERY->value => [
                    new UrlFrontierLink([
                        'url' => 'https://fedlex.data.admin.ch/api/rss-oc-de.xml',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://fedlex.data.admin.ch/api/rss-cc-de.xml',
                    ]),
                ],

                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.fedlex.admin.ch/elasticsearch/proxy/_search?index=data&gazetteupd=1',
                        // https://fedlex.data.admin.ch/api/rss-fga-de.xml
                    ]),
                ],
            ],
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function preFetch(PageCrawl $pageCrawl): void
    {
        $pageCrawl->pageUrl->url = explode('&target=', $pageCrawl->pageUrl->url)[0];

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/index=data&oc=/')) {
            $settings = $this->getDefaultOcPostData($pageCrawl);

            $pageCrawl->setHttpSettings($settings);
        }

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/cc\/internal-law/')) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => true,
                    'wait_browser' => 'networkidle0',
                    'wait_for' => '#content',
                ],
            ]);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/eli\/cc\//')) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => true,
                    'wait_browser' => 'networkidle0',
                    'wait_for' => '#lawcontent',
                ],
            ]);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/index=data&gazetteupd/')) {
            $settings = $this->getUpdatePostData($pageCrawl);

            $pageCrawl->setHttpSettings($settings);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return array<string, mixed>
     */
    protected function getDefaultOcPostData(PageCrawl $pageCrawl): array
    {
        $parsed = explode('&oc=', $pageCrawl->pageUrl->url);
        $startYear = (int) $parsed[1];
        $endYear = $startYear + 1;

        $pageCrawl->pageUrl->url = $parsed[0];

        return [
            'method' => 'POST',
            'options' => [
                'json' => [
                    'size' => 10000,
                    'aggs' => [
                        'group_by_month' => [
                            'date_histogram' => [
                                'field' => 'data.attributes.publicationDate.xsd:date',
                                'interval' => 'month',
                            ],
                        ],
                    ],
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'term' => [
                                        'data.type.keyword' => 'Act',
                                    ],
                                ],
                                [
                                    'term' => [
                                        'included.attributes.memorialName.xsd:string.keyword' => 'AS',
                                    ],
                                ],
                                [
                                    'range' => [
                                        'data.attributes.publicationDate.xsd:date' => [
                                            'gte' => "{$startYear}-01-01",
                                            'lt' => "{$endYear}-01-01",
                                        ],
                                    ],
                                ],
                            ],
                            'should' => [
                                [
                                    'exists' => [
                                        'field' => 'facets.memorialPage.http://publications.europa.eu/resource/authority/language/DEU',
                                    ],
                                ],
                                [
                                    'exists' => [
                                        'field' => 'data.attributes.sequenceInTheYearOfPublication.xsd:int',
                                    ],
                                ],
                            ],
                            'minimum_should_match' => 1,
                        ],
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
    public function parsePage(PageCrawl $pageCrawl): void
    {
        //        $pageCrawl->pageUrl->load(['frontierMeta']);

        //        $nextFunction = $pageCrawl->pageUrl->frontierMeta->meta['extra']['next_function'] ?? null;

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/index=data/')) {
            $pageCrawl->pageUrl->url .= '&target=oc';
            $this->handleCatalogueIndex($pageCrawl);
        }

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/cc\/internal-law/')) {
            $this->getLinks($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\/eli\//')) {
            $this->handleMeta($pageCrawl);
        }

        if ($this->arachno->crawlIsWorkChangesDiscovery($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\/rss-oc-de\.xml/')
            || $this->arachno->matchUrl($pageCrawl, '/\/rss-cc-de\.xml/')) {
            $this->handleChangesApplied($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\.html/')) {
            $this->capturePageContent($pageCrawl);
            $this->captureToc($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/eli\/cc\//')) {
            $this->captureInternalContent($pageCrawl);
            $this->captureToc($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/index=data/')) {
            $pageCrawl->pageUrl->url .= '&target=gazetteupd';
            $this->handleForUpdates($pageCrawl);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function getLinks(PageCrawl $pageCrawl): void
    {
        if ($pageCrawl->domCrawler->filter('.table.table-striped td a[href^="/eli"]')->count() > 0) {
            $this->handleInternalCatalogue($pageCrawl);

            return;
        }

        $this->getFirstLinks($pageCrawl);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function getFirstLinks(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.table.table-striped td a:not([href*="#"])', function (DOMElement $links) use ($pageCrawl) {
            $invalidHref = str_contains($links->getAttribute('href'), 'javascript');

            if (!$invalidHref) {
                $href = "{$this->baseLink}{$links->getAttribute('href')}";

                $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => "{$href}"]), true);
            }
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleInternalCatalogue(PageCrawl $pageCrawl): void
    {
        $docLinks = $pageCrawl->domCrawler->filter('.table.table-striped td a[href^="/eli"]');

        /** @var DOMElement $docLink */
        foreach ($docLinks as $docLink) {
            $href = $docLink->getAttribute('href');

            $title = $docLink->textContent;
            $uniqueId = Str::of($href)->after('eli/')->before('/de');

            $catDoc = new CatalogueDoc([
                'title' => $title,
                'source_unique_id' => $uniqueId,
                'view_url' => "{$this->baseLink}{$href}",
                'start_url' => "{$this->baseLink}{$href}",
                'language_code' => 'deu',
                'primary_location_id' => '161460',
            ]);

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

            $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return array<string, mixed>
     */
    protected function getAllContent(PageCrawl $pageCrawl): array
    {
        $json = $pageCrawl->getJson();

        return $json['hits']['hits'] ?? [];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleCatalogueIndex(PageCrawl $pageCrawl): void
    {
        $arr = $this->getAllContent($pageCrawl);
        $mainUrl = $pageCrawl->getFinalRedirectedUrl();
        foreach ($arr as $item) {
            $this->handleCreateOcCatalogueDoc($pageCrawl, $item, $mainUrl);
        }
    }

    /**
     * @param PageCrawl            $pageCrawl
     * @param array<string, mixed> $item
     * @param string               $mainUrl
     *
     * @return void
     */
    protected function handleCreateOcCatalogueDoc(PageCrawl $pageCrawl, array $item, string $mainUrl): void
    {
        $versions = collect($item['_source']['included'] ?? [])
            ->filter(fn ($exp) => str_ends_with($exp['uri'], '/de'))
            ->first();
        $url = $versions['uri'] ?? '';

        $htmlVersion = collect($item['_source']['included'] ?? [])
            ->filter(fn ($exp) => str_ends_with($exp['uri'], '/de/html'))
            ->first();
        $sourceVersion = $htmlVersion ?? collect($item['_source']['included'] ?? [])
            ->filter(fn ($exp) => str_ends_with($exp['uri'], '/de/pdf-a'))
            ->first();

        $sourceVersion = $sourceVersion['attributes']['isExemplifiedBy']['rdfs:Resource'] ?? null;

        $url = preg_replace('/fedlex\.data\.admin/', 'fedlex.admin', $url);

        $title = $versions['attributes']['title']['xsd:string'] ?? '';

        $id = Str::of($url)->after('eli/')->before('/de');

        if ($title && !str_starts_with($title, 'Consolidation')) {
            $catDoc = new CatalogueDoc([
                'title' => $title,
                'start_url' => $sourceVersion ?? $url,
                'view_url' => $url,
                'source_unique_id' => $id,
                'language_code' => 'deu',
                'primary_location_id' => '161460',
            ]);

            $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleChangesApplied(PageCrawl $pageCrawl): void
    {
        $uniqueIds = [];
        $this->arachno->eachX($pageCrawl, '//item', function (DOMElement $links) use (&$uniqueIds) {
            $domCrawler = new DomCrawler($links);
            /** @var DOMElement $url */
            $url = $domCrawler->filterXpath('.//link')->getNode(0);
            $url = $url->textContent;
            $id = (string) Str::of($url)->after('/eli\//');
            $id = str_replace('https://fedlex.data.admin.ch/eli/', '', $id);
            $uniqueIds[$id] = true;
        });
        $uniqueIds = array_keys($uniqueIds);
        $this->arachno->fetchChangedDocs($uniqueIds, $pageCrawl->pageUrl->crawl?->crawler?->source_id ?? 0);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc
     */
    protected function handleMeta(PageCrawl $pageCrawl): Doc
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'deu');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '161460');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->start_url);
        $workType = $this->getWorkType($catalogueDoc);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $workType);

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);

        return $doc;
    }

    /**
     * @param CatalogueDoc $doc
     *
     * @return string
     */
    protected function getWorkType(CatalogueDoc $doc): string
    {
        return match (explode('/', (string) $doc->source_unique_id)[0]) {
            'cc' => 'ordinance',
            default => 'act',
        };
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function capturePageContent(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'body',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '.erlasstitel.botschafttitel ',
            ],
        ]);

        $preStore = function (DomCrawler $crawler) {
            foreach ($crawler->filter('[id*="/"]') as $section) {
                /** @var DOMElement $section */
                $section->setAttribute('id', str_replace('/', '_', $section->getAttribute('id')));
            }

            return $crawler;
        };

        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, preStoreCallable: $preStore);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function captureInternalContent(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '#lawcontent',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//title',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//style',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[@rel="stylesheet"]',
            ],
        ]);
        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '#toolbar',
            ],
        ]);

        $preStore = function (DomCrawler $crawler) {
            foreach ($crawler->filter('[id*="/"]') as $section) {
                /** @var DOMElement $section */
                $section->setAttribute('id', str_replace('/', '_', $section->getAttribute('id')));
            }

            return $crawler;
        };

        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, $removeQueries, preStoreCallable: $preStore);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function captureToc(PageCrawl $pageCrawl): void
    {
        //        $baseUrl = $pageCrawl->pageUrl->url;
        $extractLink = function ($nodeDTO) {
            $href = $nodeDTO['node']->getAttribute('href');
            $href = explode('#', $href);

            if ($href[1] ?? false) {
                return '#' . str_replace(['#', '/'], ['', '_'], $href[1]);
            }

            return $href[0];
        };

        $extractLabel = function ($nodeDTO) {
            $parent = $nodeDTO['node']->parentNode;
            $domCrawler = new DomCrawler($parent);

            return collect($domCrawler->filterXPath('.//a[not(ancestor::sup)]')->getIterator())
                ->map(fn ($node) => $node->textContent)
                ->join(' ');
        };

        $extractUniqueId = function ($nodeDTO) {
            $href = $nodeDTO['node']?->getAttribute('href');
            $href = str_contains($href, $this->baseLink) ? Str::of($href)->after('#') : $href;

            return str_replace(['#', '/'], ['', '_'], $href);
        };

        $matchesToC = function ($nodeDTO) {
            $href = $nodeDTO['node']->getAttribute('href');
            // Comment
            // $isFootnote = preg_match('/^\s*(SR|AS|BBl)\s*/', $nodeDTO['node']->textContent ?? '') !== 0;
            $isFootnoteById = str_contains($href, 'fn');
            $isTocItem = str_contains($href, '#');

            return strtolower($nodeDTO['node']->nodeName) === 'a'
                && $href
                && $href !== '#'
                && $isTocItem
                && !$isFootnoteById;
        };

        $criteria = function ($nodeDTO) use ($matchesToC) {
            $matches = $matchesToC($nodeDTO);

            if (!$matches) {
                return false;
            }

            $parent = $nodeDTO['node']->parentNode;
            $domCrawler = new DomCrawler($parent);
            $domCrawler = $domCrawler->filterXPath('.//a[not(ancestor::sup)]');

            if ($domCrawler->count() === 2 && $domCrawler->getNode(0) !== $nodeDTO['node']) {
                return !$matchesToC(['node' => $domCrawler->getNode(0)]);
            }

            return $matches;
        };

        $this->arachno->captureTocCSS(
            $pageCrawl,
            'body > div, #lawcontent',
            $criteria,
            $extractLabel,
            $extractLink,
            $extractUniqueId,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function getUpdatePostData(PageCrawl $pageCrawl): array
    {
        $parsed = explode('&gazetteupd', $pageCrawl->pageUrl->url);
        $pageCrawl->pageUrl->url = $parsed[0];
        $date = Carbon::now()->format('Y-m');
        $endDate = Carbon::now()->endOfMonth()->toDateString();

        return [
            'method' => 'POST',
            'options' => [
                'json' => [
                    'size' => 300,
                    'sort' => [
                        [
                            'data.attributes.publicationDate.xsd:date' => [
                                'order' => 'asc',
                            ],
                        ],
                        [
                            'facets.memorialPage.http://publications.europa.eu/resource/authority/language/DEU' => [
                                'order' => 'asc',
                            ],
                        ],
                        [
                            'data.attributes.sequenceInTheYearOfPublication.xsd:int' => [
                                'order' => 'asc',
                            ],
                        ],
                    ],
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'term' => [
                                        'data.type.keyword' => 'Act',
                                    ],
                                ],
                                [
                                    'term' => [
                                        'included.attributes.memorialName.xsd:string.keyword' => 'BBl',
                                    ],
                                ],
                                [
                                    'range' => [
                                        'data.attributes.publicationDate.xsd:date' => [
                                            'gte' => "{$date}-01",
                                            'lte' => $endDate,
                                        ],
                                    ],
                                ],
                            ],
                        ],
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
    protected function handleForUpdates(PageCrawl $pageCrawl): void
    {
        $arr = $this->getAllContent($pageCrawl);
        $mainUrl = $pageCrawl->getFinalRedirectedUrl();
        foreach ($arr as $item) {
            $this->handleUpdateItems($pageCrawl, $item, $mainUrl);
        }
    }

    /**
     * @param PageCrawl            $pageCrawl
     * @param array<string, mixed> $item
     * @param string               $mainUrl
     *
     * @return void
     */
    protected function handleUpdateItems(PageCrawl $pageCrawl, array $item, string $mainUrl): void
    {
        $versions = collect($item['_source']['included'] ?? [])
            ->filter(fn ($exp) => str_ends_with($exp['uri'], '/de'))
            ->first();
        $url = $versions['uri'] ?? '';

        $url = preg_replace('/fedlex\.data\.admin/', 'fedlex.admin', $url);

        $title = $versions['attributes']['title']['xsd:string'] ?? '';

        $id = Str::of($url)->after('eli/')->before('/de');

        /** @var string $urlDate */
        $urlDate = Str::of($url)->after('fga/');
        $urlDate = str_replace('/', '-', $urlDate);
        $pdfLink = "https://www.fedlex.admin.ch/filestore/fedlex.data.admin.ch/eli/{$id}/de/pdf-a/fedlex-data-admin-ch-eli-fga-{$urlDate}-pdf-a.pdf";

        $metaDto = new DocMetaDto();
        $metaDto->title = $title;
        $metaDto->source_unique_id = $id;
        $metaDto->download_url = $pdfLink;
        $metaDto->source_url = $url;
        $metaDto->work_type = 'gazette';
        $metaDto->primary_location = '161460';
        $metaDto->language_code = 'deu';

        $link = new UrlFrontierLink(['url' => $pdfLink]);
        $link->_metaDto = $metaDto;
        $this->arachno->followLink($pageCrawl, $link, true);
    }
}
