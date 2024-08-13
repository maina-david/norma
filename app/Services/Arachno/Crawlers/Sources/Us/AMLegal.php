<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Services\Arachno\Parse\TocItemDraft;
use DOMElement;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore
 *
 * slug: us-amlegal
 * title: AM Legal
 * url: https://codelibrary.amlegal.com/
 */
class AMLegal extends AbstractCrawlerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/codelibrary\.amlegal\.com\/$/')) {
            $this->handleCatalogueStateIndex($pageCrawl);
        }
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/amlegal\.com\/api\/client-regions/')) {
            $this->handleCatalogueCitiesInStateIndex($pageCrawl);
        }
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/amlegal\.com\/api\/clients\//')) {
            $this->handleCatalogueCodesForClient($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/amlegal\.com\/api\/code\-versions\//')) {
            $this->handleClientDetails($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/amlegal\.com\/api\/section\-toc\//')) {
            $json = $pageCrawl->getJson();
            [$clientSlug, $slug, $_] = explode('|', $pageCrawl->pageUrl->anchor_text ?? '');
            $docId = $json['orig_doc_id'] ?? $json['doc_id'];
            $this->handleAMLegalTocItems($pageCrawl, $json['children'], $clientSlug, $slug, $docId);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/amlegal\.com\/api\/render\-section\//')) {
            $this->handleAMLegalContent($pageCrawl);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl) && !$this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $this->fetchInitialLinks($pageCrawl);
            $this->handleUpdates($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-amlegal',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://codelibrary.amlegal.com',
                    ]),
                ],

                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink([
                        'url' => 'https://codelibrary.amlegal.com/api/render-section/san_diego/latest/sandiego_ords/0-0-0-138/0/',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://codelibrary.amlegal.com/api/render-section/sanbernardino/latest/sanberncty_ca/0-0-0-178014/0/',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://codelibrary.amlegal.com/api/render-section/san_francisco/latest/sf_admin/0-0-0-25283/1/',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://codelibrary.amlegal.com/api/render-section/brea/latest/brea_ca/0-0-0-74322/0/',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://codelibrary.amlegal.com/api/render-section/los_angeles/latest/lamc/0-0-0-198503/23/',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://codelibrary.amlegal.com/api/render-section/elsegundoca/latest/elsegundo_ca/0-0-0-22011/0/',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://codelibrary.amlegal.com/api/render-section/oxnard/latest/oxnard_ca/0-0-0-86299/25/',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://codelibrary.amlegal.com/api/render-section/thousandoaks/latest/thousandoaks_ca/0-0-0-21781/0/',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://codelibrary.amlegal.com/api/render-section/beverlyhillsca/latest/beverlyhills_ca/0-0-0-27867/0/',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://codelibrary.amlegal.com/api/render-section/cupertino/latest/cupertino_ca/0-0-0-96235/0/',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://codelibrary.amlegal.com/api/render-section/paloalto/latest/paloalto_ca/0-0-0-84477/0/',
                    ]),
                ],
            ],
            'css' => '.toc-destination {font-weight:bold;}',
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleCatalogueStateIndex(PageCrawl $pageCrawl): void
    {
        $this->arachno->eachX($pageCrawl, '//*[contains(@class,"am-page__content")]//*[contains(@class,"browse-columns")]//*[contains(@class,"browse-link")]', function ($link) use ($pageCrawl) {
            $href = $link->getAttribute('href');
            $state = str_replace(['/regions/'], '', $href);
            $url = 'https://codelibrary.amlegal.com/api/client-regions/' . $state . '/';
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $url, 'anchor_text' => '']));
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleCatalogueCitiesInStateIndex(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $state = $json['slug'];
        foreach ($json['clients'] as $client) {
            $slug = $client['slug'];
            $url = 'https://codelibrary.amlegal.com/api/clients/' . $slug . '/';
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $url, 'anchor_text' => $state]));
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleCatalogueCodesForClient(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $version = $json['versions'][0]['uuid'] ?? '';
        // we used the anchor text for the state info
        $state = $pageCrawl->pageUrl->anchor_text ?? '';

        if ($version !== '') {
            /** @var array<mixed> */
            $response = $this->arachno->fetchLink($pageCrawl, 'https://codelibrary.amlegal.com/api/code-versions/' . $version);

            foreach ($response['toc'] as $code) {
                $title = $code['title'];
                $pageUrl = $pageCrawl->pageUrl->url;
                $prefix = (string) Str::of($pageUrl)->after('clients/')->before('/');
                $locations = $this->getAMLegalLocations();
                $primaryLocation = $locations[$prefix] ?? null;
                if ($primaryLocation !== null) {
                    $catDoc = new CatalogueDoc([
                        'title' => trim($title),
                        'start_url' => 'https://codelibrary.amlegal.com/api/code-versions/' . $version . '/',
                        'view_url' => 'https://codelibrary.amlegal.com/codes/' . $code['client_slug'] . '/latest/' . $code['slug'],
                        'source_unique_id' => $code['client_slug'] . '-' . $code['slug'],
                        'language_code' => 'eng',
                        'post_data' => ['uuid' => $code['uuid'], 'prefix' => $prefix],
                        'primary_location_id' => $primaryLocation,
                    ]);
                    $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

                    // state abbreviation was stored in meta work_number
                    $stateAbbrev = 'USA Cities and Counties - ' . strtoupper($state);
                    $this->arachno->addCategoriesToCatalogueDoc($catalogueDoc, [$stateAbbrev]);
                }
            }
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleClientDetails(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        /** @var CatalogueDoc */
        $catDoc = $pageCrawl->pageUrl->catalogueDoc;
        // This changed so while fetching it could not match what was saved in catalogue so will use source_unique_id instead
        // $codeUUID = $catDoc->post_data['uuid'] ?? '';

        $codeData = [];
        foreach ($json['toc'] as $item) {
            if ($item['client_slug'] . '-' . $item['slug'] === $catDoc->source_unique_id) {
                $codeData = $item;
                break;
            }
        }

        // Incase some jurisdictions were added without location id so that we don't need to run full catalogue crawl again
        $prefix = $catDoc->post_data['prefix'] ?? '';
        $locations = $this->getAMLegalLocations();
        $primaryLocationId = $locations[$prefix] ?? null;
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catDoc->source_unique_id ?? '');
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catDoc->title ?? '');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catDoc->view_url ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'code');
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', $primaryLocationId);
        $this->arachno->saveDoc($pageCrawl);

        $this->handleAMLegalTocItems($pageCrawl, $codeData['sections'], $codeData['client_slug'], $codeData['slug']);
    }

    /**
     * @param PageCrawl    $pageCrawl
     * @param array<mixed> $children
     * @param string       $clientSlug
     * @param string       $slug
     * @param string|null  $docId
     *
     * @return void
     */
    public function handleAMLegalTocItems(PageCrawl $pageCrawl, array $children, string $clientSlug, string $slug, ?string $docId = null): void
    {
        $catDoc = $pageCrawl->pageUrl->catalogueDoc;
        $items = [];
        $itemsAdded = false;
        foreach ($children as $index => $child) {
            $data = [
                'source_url' => ($catDoc->view_url ?? '') . '/' . $child['doc_id'],
                'label' => $child['title'],
                'source_unique_id' => $child['id'],
                'parent' => $pageCrawl->pageUrl->parentTocItem,
            ];
            if ($child['has_children']) {
                $l = new UrlFrontierLink([
                    'url' => 'https://codelibrary.amlegal.com/api/section-toc/' . $child['id'],
                    'anchor_text' => $clientSlug . '|' . $slug . '|' . $child['title'],
                ]);

                $data['links'] = [$l];
            } else {
                $data['href'] = 'https://codelibrary.amlegal.com/api/render-section/' . $clientSlug . '/latest/' . $slug . '/' . $child['doc_id'] . '/0/';
            }
            $itemsAdded = true;
            $items[] = new TocItemDraft($data);
        }
        if ($itemsAdded) {
            $this->arachno->captureTocFromDraftArray($pageCrawl, $items);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleAMLegalContent(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::FUNC,
                'query' => fn () => $json['html'],
            ],
        ]);
        $headQueries = [];
        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'CodeOptions,AnnotationDrawer',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//*[(contains(text(), "Published by") or child::*[contains(text(), "Published by")] or child/child::*[contains(text(), "Published by")]) and (descendant::text()[contains(.,"American Legal Publishing")] or descendant::text()[contains(.,"AMERICAN LEGAL PUBLISHING")])]',
            ],
        ]);

        $preStore = function ($domCrawler) {
            foreach ($domCrawler->filterXpath('//*[@classname]') as $domElement) {
                /** @var DOMElement $domElement */
                $domElement->setAttribute('class', $domElement->getAttribute('classname'));
                $domElement->removeAttribute('classname');
            }

            return $domCrawler;
        };
        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, $removeQueries, null, $preStore);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string, int>
     */
    protected function getAMLegalLocations(): array
    {
        return [
            'wakecounty' => 4380,
            'mansfield' => 173448,
            'brooklynpark' => 60313,
            'lakecountyil' => 3042,
            'san_diego' => 2621,
            'sanbernardino' => 2620,
            'san_francisco' => 69185,
            'brea' => 69106,
            'los_angeles' => 22031,
            'elsegundoca' => 68999,
            'oxnard' => 69260,
            'thousandoaks' => 21888,
            'beverlyhillsca' => 69016,
            'cupertino' => 69210,
            'paloalto' => 69215,
            'newyorkcity' => 64723,
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function fetchInitialLinks(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $html = $json['html'];
        $year = now()->format('Y');
        preg_match("/(pathname:\s*([^,]+),\s*hash:\s*'#.+{$year}.*')/", $html, $matches);

        if (isset($matches[2])) {
            $url = $matches[2];
            $url = str_replace('/codes', '', $url);
            $url = "https://codelibrary.amlegal.com/api/render-section{$url}/0";
            $url = str_replace("'", '', $url);

            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $url]));
        }
    }

    protected function handleUpdates(PageCrawl $pageCrawl): void
    {
        $docMetaDto = new DocMetaDto();
        $json = $pageCrawl->getJson();
        $html = $json['html'];
        $matches = [];
        preg_match_all('/href="[^"]+\.pdf/', $html, $matches);

        if (empty($matches[0])) {
            return;
        }

        foreach ($matches[0] as $url) {
            $url = str_replace('href="', '', $url);
            /** @var string $url */
            $url = str_replace('"', '', $url);

            $uniqueId = Str::of($url)->afterLast('/')->before('.pdf');

            $prefix = (string) Str::of($pageCrawl->pageUrl->url)->after('render-section/')->before('/');
            $locations = $this->getAMLegalLocations();
            $primaryLocation = $locations[$prefix] ?? null;

            $docMetaDto->title = "{$prefix}-{$uniqueId}";
            $docMetaDto->source_unique_id = "updates_{$uniqueId}";
            $docMetaDto->source_url = $url;
            $docMetaDto->download_url = $url;
            $docMetaDto->primary_location = (string) $primaryLocation;
            $docMetaDto->language_code = 'eng';
            $docMetaDto->work_number = $uniqueId;
            $docMetaDto->work_type = 'ordinance';

            $link = new UrlFrontierLink(['url' => $url]);
            $link->_metaDto = $docMetaDto;
            $this->arachno->followLink($pageCrawl, $link, true);
        }
    }
}
