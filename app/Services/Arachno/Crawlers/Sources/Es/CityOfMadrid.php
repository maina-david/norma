<?php

namespace App\Services\Arachno\Crawlers\Sources\Es;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 *
 * slug: es-city-of-madrid
 * title: Spain City of Madrid
 * url: https://sede.madrid.es
 */
class CityOfMadrid extends AbstractCrawlerConfig
{
    protected string $baseDomain = 'https://sede.madrid.es';

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'es-city-of-madrid',
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink([
                        'url' => 'https://sede.madrid.es/portal/site/tramites/menuitem.944fd80592a1301b7ce0ccf4a8a409a0/?vgnextoid=741d814231ede410VgnVCM1000000b205a0aRCRD&vgnextchannel=741d814231ede410VgnVCM1000000b205a0aRCRD&vgnextfmt=default',
                    ]),
                ],
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.madrid.es/portales/munimadrid/es/Inicio/El-Ayuntamiento/Calidad-normativa/Novedades/?vgnextfmt=default&vgnextoid=fbe435aa4a066710VgnVCM1000001d4a900aRCRD&vgnextchannel=e4215dc91aa10710VgnVCM2000001f4a900aRCRD',
                    ]),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchCSS($pageCrawl, 'ul li:first-child div.info-item a[title*="BOAM"]')) {
                $this->followLink($pageCrawl);
            }
            if ($this->arachno->matchCSS($pageCrawl, 'ul li.asociada-item a')) {
                $this->handleUpdates($pageCrawl);
            }
        }
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) || $this->arachno->crawlIsNewCatalogueWorkDiscovery($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/madrid\.es\/portales\/munimadrid\/es\/Inicio\/El-Ayuntamiento\/Calidad-normativa\/Novedades/')) {
            $this->handleCatalogue($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            if ($this->arachno->matchCSS($pageCrawl, 'div.statictree-body > ul > li:nth-child(1) div.statictree-header a[href*="/sites/v/index"]')) {
                $this->getHtmlLink($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/sede\.madrid\.es\/sites\/v\/index\.jsp\?vgnextfmt\=default\&vgnextchannel/')) {
                $this->handleMeta($pageCrawl);
                $this->handleCapture($pageCrawl);
                $this->handleToc($pageCrawl);
            }
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
        $domCrawler = $pageCrawl->domCrawler;
        $title = $domCrawler->filter('h3.summary-title')->getNode(0)?->textContent ?? '';
        $date = $title ? Str::of($title)->after('(')->before(')') : '';
        /** @var DOMElement $link */
        $link = $domCrawler->filter('ul li.asociada-item a')->getNode(0);
        $link = $link->getAttribute('href');
        $uniqueId = Str::of($link)->after('Ficheros PDF/')->replace('.pdf', '');
        $sourceUrl = $pageCrawl->pageUrl->url;
        $downloadUrl = $this->baseDomain . $link;
        $docMetaDto = new DocMetaDto();

        $docMetaDto->title = $title;
        $docMetaDto->source_url = $sourceUrl;
        $docMetaDto->download_url = $downloadUrl;
        $docMetaDto->source_unique_id = 'updates_' . $uniqueId;
        $docMetaDto->language_code = 'spa';
        $docMetaDto->primary_location = '167308';
        $docMetaDto->work_type = 'order';
        if ($date) {
            try {
                /** @var Carbon */
                $workDate = Carbon::createFromFormat('d/m/Y', $date);
                $docMetaDto->work_date = $workDate;
            } catch (InvalidFormatException $th) {
            }
        }

        $l = new UrlFrontierLink(['url' => $downloadUrl, 'anchor_text' => 'pdf']);
        $l->_metaDto = $docMetaDto;
        $this->arachno->followLink($pageCrawl, $l, true);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'ul li div.event-info', function (DOMElement $item) use ($pageCrawl) {
            $entryCrawler = new DomCrawler($item);
            /** @var DOMElement */
            $viewLink = $entryCrawler->filter('a')->getNode(0);
            $viewUrl = $viewLink->getAttribute('href');
            $uniqueId = Str::of($viewUrl)->after('vgnextoid=')->before('&vgnextchannel=');
            $title = $entryCrawler->filter('a')->getNode(0)?->textContent ?? '';
            $date = $entryCrawler->filter('ul li:nth-child(2) span')->getNode(0)?->textContent ?? '';
            $date = $date ? Str::of($date)->after('Fecha de disposición:') : '';
            $catDoc = new CatalogueDoc([
                'title' => trim($title),
                'start_url' => $viewUrl,
                'view_url' => $viewUrl,
                'source_unique_id' => $uniqueId,
                'language_code' => 'spa',
                'primary_location_id' => 167308,
            ]);

            $meta = [
                'work_date' => trim($date),
            ];

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc
     */
    protected function handleMeta(PageCrawl $pageCrawl): Doc
    {
        /** @var CatalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        /** @var CatalogueDocMeta */
        $catDocMeta = CatalogueDocMeta::find($catalogueDoc->id);
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'spa');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '167308');
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', (string) 'order');
        $url = $pageCrawl->pageUrl->url;
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $url);
        $date = $catDocMeta->doc_meta['work_date'] ?? '';
        if ($date) {
            try {
                /** @var Carbon */
                $workDate = Carbon::createFromFormat('d/m/Y', $date);
                $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $workDate);
            } catch (InvalidFormatException $th) {
            }
        }

        return $doc;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function followLink(PageCrawl $pageCrawl): void
    {
        /** @var DOMElement */
        $pdfLink = $pageCrawl->domCrawler->filter('ul li:first-child div.info-item a[title*="BOAM"]')->getNode(0);
        $pdfLink = $pdfLink->getAttribute('href');
        $link = new UrlFrontierLink(['url' => "{$this->baseDomain}{$pdfLink}"]);
        $this->arachno->followLink($pageCrawl, $link);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc
     */
    protected function getDownloadLink(PageCrawl $pageCrawl): Doc
    {
        $domCrawler = $pageCrawl->domCrawler;

        /** @var DOMElement $pdfLinkEl */
        $pdfLinkEl = $domCrawler->filter('ul li:first-child.asociada-item a[href*=".pdf"]')->getNode(0);
        $pdfLink = "{$this->baseDomain}{$pdfLinkEl->getAttribute('href')}";
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $pdfLink);
        $l = new UrlFrontierLink(['url' => $pdfLink]);
        $this->arachno->followLink($pageCrawl, $l, true);

        return $doc;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function getHtmlLink(PageCrawl $pageCrawl): void
    {
        $domCrawler = $pageCrawl->domCrawler;
        /** @var DOMElement $htmlinkEl */
        $htmlinkEl = $domCrawler->filter('div.statictree-body > ul > li:nth-child(1) div.statictree-header a[href*="/sites/v/index"]')->getNode(0);
        $htmlinkEl = "{$this->baseDomain}{$htmlinkEl->getAttribute('href')}";
        $l = new UrlFrontierLink(['url' => $htmlinkEl]);
        $this->arachno->followLink($pageCrawl, $l);
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
                'query' => 'div.normativa',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'head > title',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href,".css") and @rel="stylesheet"]',
            ],
        ]);
        $preStore = function (DomCrawler $crawler) {
            $tocHeads = $crawler->filter('div p')->getNode(0) ?? null;
            if ($tocHeads !== null) {
                while ($tocHeads !== null) {
                    if (trim($tocHeads->textContent) === 'ÍNDICE') {
                        if ($tocHeads->nextSibling !== null) {
                            $tocHeads = $tocHeads->nextSibling;
                            if ($tocHeads->previousSibling !== null && $tocHeads->nextSibling !== null) {
                                $tocHeads->previousSibling->nodeValue = '';
                                $tocHeads->nextSibling->nodeValue = '';
                                $tocHeads->nodeValue = '';
                            }
                        }

                        // Continue removing until "PREÁMBULO" is found
                        while ($tocHeads !== null) {
                            if (trim($tocHeads->textContent) === 'PREÁMBULO') {
                                break; // Stop removing when "PREÁMBULO" is found
                            }
                            $nextNode = $tocHeads->nextSibling;
                            $tocHeads->parentNode?->removeChild($tocHeads);
                            $tocHeads = $nextNode;
                        }
                    } else {
                        $tocHeads = $tocHeads->nextSibling;
                    }
                }
            }

            return $crawler;
        };
        $this->arachno->capture(
            $pageCrawl,
            $bodyQueries,
            $headQueries,
            preStoreCallable: $preStore
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
            $href = $nodeDTO['node']?->getAttribute('href');

            return "{$this->baseDomain}{$href}";
        };
        $extractLabel = function ($nodeDTO) {
            $liParent = $nodeDTO['node']?->parentNode;

            return $liParent->textContent;
        };
        $this->arachno->captureTocCSS(
            $pageCrawl,
            'div.statictree-body:first-child',
            extractLabelFunc: $extractLabel,
            extractLinkFunc: $extractLink,
        );
    }
}
