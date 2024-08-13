<?php

namespace App\Services\Arachno\Crawlers\Sources\Nl;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DomQuery;
use DOMElement;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore
 *
 * slug: nl-wetten-overheid
 * title: Wetten Overheid
 * url: https://wetten.overheid.nl/.
 */
class WettenOverheid extends AbstractCrawlerConfig
{
    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'nl-wetten-overheid',
            'throttle_requests' => 100,
            'start_urls' => [
                'type_' . CrawlType::SEARCH->value => [
                    new UrlFrontierLink([
                        'url' => 'https://wetten.overheid.nl/zoeken/zoekresultaat/rs/2,3,4,5,6,9/titelf/1/tekstf/1/d/' . now()->format('d-m-Y') . '/dx/0/page/1/count/200/s/2',
                    ]),
                ],
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://wetten.overheid.nl/zoeken/zoekresultaat/rs/2,3,4,5,6,9/titelf/1/tekstf/1/d/' . now()->format('d-m-Y') . '/dx/0/page/1/count/200/s/2',
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
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/zoekresultaat/')) {
                $this->handleCatalogue($pageCrawl);
            }
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            if ($this->arachno->matchCSS($pageCrawl, '#regeling')) {
                $this->handleMeta($pageCrawl);
                $this->handleCapture($pageCrawl);
                $this->handleToc($pageCrawl);
            }
        }
        if ($this->arachno->crawlIsSearch($pageCrawl)) {
            $this->handleSearch($pageCrawl);
        }
    }

    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => false,
                ],
            ]);

            if ($this->arachno->matchCSS($pageCrawl, '#sidebar #treeview-')) {
                $pageCrawl->setProxySettings([
                    'provider' => 'scraping_bee',
                    'options' => [
                        'render_js' => false,
                    ],
                ]);
            }
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.result--list a.result--title', function (DOMElement $link) use ($pageCrawl) {
            /** @var DOMElement $link */
            $href = $link->getAttribute('href');
            // e.g. /id/BWBR0021409/2007-03-09/0?g=2023-01-03&z=2023-01-03
            $id = Str::of($href)->match('/\/id\/([a-zA-Z0-9]+)\//');

            $catDoc = new CatalogueDoc([
                'title' => trim($link->textContent),
                'start_url' => 'https://wetten.overheid.nl/' . $id,
                'view_url' => 'https://wetten.overheid.nl/' . $id,
                'source_unique_id' => (string) $id,
                'language_code' => 'nld',
            ]);
            $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
        });

        // go to next page of search results
        $this->arachno->followLinksCSS($pageCrawl, '.pagination__index .next a');
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc
     */
    protected function handleMeta(PageCrawl $pageCrawl): Doc
    {
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        /** @var DOMElement $title */
        $title = $pageCrawl->domCrawler->filterXPath('//head//meta[@name="dcterms:title"]/@content')->getNode(0);
        $title = $title->textContent;

        $doc = $this->arachno->setDocMetaPropertyByXpath($pageCrawl, 'source_unique_id', '//head//meta[@name="dcterms:identifier"]/@content');
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'nld');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title ?? $title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '42319');
        $url = $pageCrawl->getFinalRedirectedUrl();
        $workDate = Str::afterLast(Str::beforeLast($url, '/'), '/');
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $workDate);

        $domCrawler = $pageCrawl->domCrawler;
        $typeDutch = $domCrawler->filterXPath('//head//meta[@name="dcterms:type"]/@content')->getNode(0)?->textContent;
        $workType = match ($typeDutch) {
            'wet' => 'act',
            'reglement' => 'regulation',
            'beleidsregel' => 'rule',
            'circulaire' => 'notice',
            default => 'act',
        };
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $workType);
        $this->arachno->setDocMetaPropertyByCSS($pageCrawl, 'summary', '#Opschrift');
        $this->arachno->setDocMetaPropertyByXpath($pageCrawl, 'work_number', '//head//meta[@name="dcterms:identifier"]/@content');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $pageCrawl->getFinalRedirectedUrl());

        return $doc;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCapture(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '#regeling',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href,"generic") and @rel="stylesheet"]',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href,"wetten") and @rel="stylesheet"]',
            ],
        ]);
        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '.article__header--law ul',
            ],
        ]);
        $this->arachno->capture(
            $pageCrawl,
            $bodyQueries,
            $headQueries,
            $removeQueries,
        );
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleToc(PageCrawl $pageCrawl): void
    {
        $extractLink = function ($nodeDTO) {
            $id = $nodeDTO['node']->getAttribute('id');

            /** @var string $id */
            $id = Str::replace(['TOC_', 'TOC'], '', $id); // @phpstan-ignore-line

            return '#' . $id;
        };
        $extractLabel = function ($nodeDTO) {
            $n = $nodeDTO['node'];
            $inlines = [];
            foreach ($n->childNodes as $child) {
                if ($child instanceof DOMElement && ($child->nodeName === 'button' || $child->nodeName === 'span')) {
                    continue;
                }
                $inlines[] = $child->textContent;
            }

            return preg_replace('/\s+/', ' ', implode(' ', $inlines));
        };
        $extractUniqueId = function ($nodeDTO) {
            $uniqueId = $nodeDTO['node']?->getAttribute('id');

            return str_contains($uniqueId, 'TOC') ? str_replace('TOC', '', $uniqueId) : $uniqueId;
        };
        $this->arachno->captureTocCSS(
            $pageCrawl,
            '#sidebar #treeview-',
            null,
            $extractLabel,
            $extractLink,
            $extractUniqueId
        );
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleSearch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/zoekresultaat/')) {
            // go to next page of search results
            $this->arachno->followLinksCSS($pageCrawl, '.pagination__index .next a');
            $this->arachno->followLinksCSS($pageCrawl, '.result--list a.result--title');
        } else {
            $this->arachno->indexForSearch(
                $pageCrawl,
                contentQuery: new DomQuery(['query' => '#regeling', 'type' => DomQueryType::CSS]),
                primaryLocationId: 42319,
                languageCode: 'nld',
            );
        }
    }
}
