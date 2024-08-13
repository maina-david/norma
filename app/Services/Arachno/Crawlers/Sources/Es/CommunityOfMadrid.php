<?php

namespace App\Services\Arachno\Crawlers\Sources\Es;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use DOMElement;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 *
 * slug: es-community-of-madrid
 * title: Spain Community of Madrid
 * url: https://www.bocm.es/
 */
class CommunityOfMadrid extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'es-community-of-madrid',
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.bocm.es/ordenes-ultimo-boletin',
                    ]),
                ],
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.bocm.es/search-free',
                    ]),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/bocm\.es\/ordenes-ultimo-boletin/')) {
            $this->handleUpdates($pageCrawl);
        }
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) || $this->arachno->crawlIsNewCatalogueWorkDiscovery($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/bocm\.es\/search-free/')) {
            $this->handleCatalogue($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\.bocm\.es\/bocm[a-z0-9-]+/')) {
            $this->handleMeta($pageCrawl);
            $this->handleCapture($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/\.pdf/i')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleUpdates(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'div.views-row', function (DOMElement $element) use ($pageCrawl) {
            $domCrawler = new DomCrawler($element);

            $title = $domCrawler->filter('div.views-field-nothing-1 span.field-content')->getNode(0)?->textContent;
            $description = $domCrawler->filter('div.views-field-nothing-2 span.field-content')->getNode(0)?->textContent;
            /** @var DOMElement $link */
            $link = $domCrawler->filter('span a[href*="/bocm"]')->getNode(0);
            $link = $link->getAttribute('href');
            $uniqueId = Str::of($link)->after('/');
            $sourceUrl = 'https://www.bocm.es' . $link;
            $linkUrl = strtoupper($link);
            $baseDomain = 'https://www.bocm.es/boletin/CM_Orden_BOCM/' . now()->format('Y/m/d');
            $link = "{$baseDomain}{$linkUrl}.PDF";
            $docMetaDto = new DocMetaDto();

            $docMetaDto->title = $title;
            $docMetaDto->source_url = $sourceUrl;
            $docMetaDto->summary = $description;
            $docMetaDto->download_url = $link;
            $docMetaDto->source_unique_id = 'updates_' . $uniqueId;
            $docMetaDto->work_number = $uniqueId;
            $docMetaDto->language_code = 'spa';
            $docMetaDto->primary_location = '167229';
            $docMetaDto->work_type = 'order';

            $link = new UrlFrontierLink(['url' => $link, 'anchor_text' => 'pdf']);
            $link->_metaDto = $docMetaDto;
            $this->arachno->followLink($pageCrawl, $link, true);
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'div.views-row', function (DOMElement $item) use ($pageCrawl) {
            $entryCrawler = new DomCrawler($item);
            /** @var DOMElement */
            $pdfLink = $entryCrawler->filter('a[href*=".PDF"]')->getNode(0);
            /** @var DOMElement */
            $viewLink = $entryCrawler->filter('a[href*="/bocm-"]')->getNode(0);
            $pdfHref = $pdfLink->getAttribute('href');
            $viewUrl = $viewLink->getAttribute('href');
            $viewUrl = 'https://www.bocm.es' . $viewUrl;
            $uniqueId = Str::of($pdfHref)->afterLast('/')->replace('.PDF', '');
            $title = $entryCrawler->filter('div.field-name-field-short-description div.field-item')->getNode(0)?->textContent ?? '';
            $catDoc = new CatalogueDoc([
                'title' => trim($title),
                'start_url' => $viewUrl,
                'view_url' => $viewUrl,
                'source_unique_id' => $uniqueId,
                'language_code' => 'spa',
                'primary_location_id' => 167229,
            ]);

            $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
        });
        // go to Next Page
        $this->arachno->followLinksCSS($pageCrawl, 'ul li.next a');
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleMeta(PageCrawl $pageCrawl): void
    {
        /** @var CatalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'spa');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '167229');
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'order');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        /** @var DOMElement */
        $pdfLink = $pageCrawl->domCrawler->filter('a[href*=".PDF"]')->getNode(0);
        $pdfHref = $pdfLink->getAttribute('href');
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $pdfHref);
        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
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
                'query' => 'div#cuerpopopup',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//title',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href,"boletin/css") and @rel="stylesheet"]',
            ],
        ]);
        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'div.cabecera_popup',
            ],
        ]);
        $this->arachno->capture(
            $pageCrawl,
            $bodyQueries,
            $headQueries,
            $removeQueries,
        );
    }
}
