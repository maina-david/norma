<?php

namespace App\Services\Arachno\Crawlers\Sources\Es;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Services\Arachno\Parse\DomQuery;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 *
 * slug: es-boe
 * title: spain State official newsletter
 * url: https://boe.es
 */
class SpainBoletinOficial extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'es-boe',
            'throttle_requests' => 300,
            'verify_ssl' => false,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink([
                        'url' => 'https://boe.es/boe/dias/' . now()->format('Y/m/d') . '/index.php?s=1',
                    ]),
                ],
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.boe.es/biblioteca_juridica/index.php?tipo=C',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://www.boe.es/buscar/legislacion.php?accion=Mas&id_busqueda=THFXbWllaklWMmlBSy9CU1hObVFoQ1B2TGRXdzJFdGlodFo5elh6SHRMNk5neTZ4QmZ
                        IY1FSYkdnb09UdjcyNkp4VkJYRUVvUENQVkpGRjhpdW5Dc2Q0NE5KQ0tRa2RLT2pjNUF5VENQZkpqMkJjekkxYWhTSENDS2xVY3ZDVHpUaWdaVDN4bFphNmFaQmVtcG9WTnFxd21WZURqV
                        EhNUTFGNDdwTEFnV25vK1hQVUNaQ1ZaaURYRThMS1NKUm5j-0-2000&page_hits=2000&sort_field%5B0%5D=FPU&sort_order%5B0%5D=desc',
                    ]),
                ],
                'type_' . CrawlType::SEARCH->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.boe.es/biblioteca_juridica/index.php?tipo=C',
                    ]),
                ],
            ],
        ];
    }

    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/biblioteca_juridica\//')
            || $this->arachno->matchUrl($pageCrawl, '/buscar\/legislacion\.php\?accion=Mas/')
            || $this->arachno->matchUrl($pageCrawl, '/buscar\/(?:act|doc)\.php\?id=/')) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => ['render_js' => false],
            ]);
        }
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/biblioteca_juridica/')) {
                $this->getInitialLinks($pageCrawl);
                $this->handleCatalogue($pageCrawl);
            }

            if ($this->arachno->matchUrl($pageCrawl, '/buscar\/legislacion\.php\?accion=Mas/')) {
                $this->handleLegislationCatalogue($pageCrawl);
            }
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\/boe\/dias\//')) {
            $this->handleUpdates($pageCrawl);
        }

        if ($this->arachno->crawlIsSearch($pageCrawl)) {
            $this->getInitialLinks($pageCrawl);
            $this->handleSearch($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/buscar\/(?:act|doc)\.php\?id=[^&]+$/')) {
            $this->followToDoc($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/buscar\/(?:act|doc)\.php\?id=/')) {
            $this->getPageContent($pageCrawl);

            if ($this->arachno->matchUrl($pageCrawl, '/&p=[0-9]+&tn=2/')) {
                $this->handleToc($pageCrawl);
            }
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    protected function handleSearch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/biblioteca_juridica/')) {
            $this->arachno->eachX($pageCrawl, '//*[@id="col-2"]/ol/li/a[contains(@href, "/buscar/act.php")]', function (DOMElement $list) use ($pageCrawl) {
                $domCrawler = new DomCrawler($list);
                /** @var DOMElement $href */
                $href = $domCrawler->filter('a')->getNode(0);
                $link = "https://www.boe.es{$href->getAttribute('href')}";

                $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $link]));
            });
        } else {
            if ($this->arachno->matchUrl($pageCrawl, '/buscar\/act\.php\?id=/')) {
                $this->arachno->indexForSearch(
                    $pageCrawl,
                    new DomQuery(['query' => '#textoxslt', 'type' => DomQueryType::CSS]),
                    163985,
                    'spa'
                );
            }
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function getInitialLinks(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'li.etiqueta', function (DOMElement $list) use ($pageCrawl) {
            $domCrawler = new DomCrawler($list);

            /** @var DOMElement $href */
            $href = $domCrawler->filter('a')->getNode(0);
            $href = $href->getAttribute('href');
            $link = "https://www.boe.es{$href}&tab=2";

            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $link]));
        });
    }

    protected function followNextLink(PageCrawl $pageCrawl): void
    {
        /** @var DOMElement|null $nextPageElem */
        $nextPageElem = $pageCrawl->domCrawler->filter('span.pagSig')->getNode(0);

        if ($nextPageElem && $nextPageElem->parentNode) {
            /** @var DOMElement $parent */
            $parent = $nextPageElem->parentNode;
            $nextHref = 'https://www.boe.es/buscar/' . $parent->getAttribute('href');
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $nextHref]));
        }
    }

    /**
     * @param PageCrawl  $pageCrawl
     * @param DOMElement $links
     *
     * @return void
     */
    protected function addToCatalogue(PageCrawl $pageCrawl, DOMElement $links): void
    {
        $domCrawler = new DomCrawler($links);

        /** @var DOMElement $anchorElem */
        $anchorElem = $domCrawler->filterXPath('.//a')->getNode(0);
        $href = $anchorElem->getAttribute('href');
        $href = str_replace('..', '', $href);
        $href = 'https://www.boe.es' . $href;
        $title = $anchorElem->textContent;

        $uniqueId = Str::of($href)->after('id=');

        $catDoc = new CatalogueDoc([
            'title' => $title,
            'view_url' => $href,
            'start_url' => $href,
            'source_unique_id' => $uniqueId,
            'language_code' => 'spa',
            'primary_location_id' => '163985',
        ]);

        $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

        $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/buscar\/legislacion\.php\?accion=Mas/')) {
            $this->followNextLink($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->eachX($pageCrawl, '//*[@id="col-2"]/ol/li/a[contains(@href, "/buscar")]',
            function (DOMElement $links) use ($pageCrawl) {
                $this->addToCatalogue($pageCrawl, $links);
            });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleLegislationCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->eachX($pageCrawl, '//*[@id="contenido"]/div/ul/li/ul/li[contains(@class, "puntoHTML")]',
            function (DOMElement $links) use ($pageCrawl) {
                $this->addToCatalogue($pageCrawl, $links);
            });
    }

    protected function getPageContent(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '#textoxslt',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//title',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[@rel="stylesheet"][contains(@href, "consolidada")]',
            ],
        ]);
        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '.marcadores',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'blockquote',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '.linkSubir',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '.lista.formBOE',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'label[for*="btn_jur"]',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//p[@class="bloque"]',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[@rel="stylesheet"][contains(@href, "boe")]',
            ],
        ]);

        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, $removeQueries);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleToc(PageCrawl $pageCrawl): void
    {
        $extractLink = function ($nodeDTO) {
            $link = $nodeDTO['node']->getAttribute('href');
            $id = Str::of($link)->after('&tn=1');

            return preg_replace('/&p=[0-9]+&tn=1#.*/', $id, $link);
        };

        $extractUniqueId = function ($nodeDTO) {
            $href = $nodeDTO['node']->getAttribute('href');

            return Str::of($href)->after('#');
        };

        //        $determineDepth = function ($nodeDTO) {
        //            $href = $nodeDTO['node']->getAttribute('href');
        //            $id = explode('#', $href)[1] ?? '';
        //            $id = substr($id, 0, 1);
        //
        //            return match ($id) {
        //                'c' => 2,
        //                's' => 3,
        //                'a' => 4,
        //                default => 1,
        //            };
        //        };

        $this->arachno->captureTocCSS(
            $pageCrawl,
            '#textoindice',
            extractLinkFunc: $extractLink,
            extractUniqueIdFunc: $extractUniqueId,
        );
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handleUpdates(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.sumario li.dispo', function (DOMElement $element) use ($pageCrawl) {
            $domCrawler = new DomCrawler($element);

            $title = $domCrawler->filterXPath('.//p')->getNode(0)?->textContent;
            $title = $title ? trim($title) : '';
            /** @var DOMElement $link */
            $link = $domCrawler->filter('li.puntoPDF a')->getNode(0);
            $link = $link->getAttribute('href');
            $uniqueId = Str::of($link)->after('pdfs/')->before('.pdf');

            $baseDomain = 'https://boe.es';
            $link = "{$baseDomain}{$link}";

            $docMetaDto = new DocMetaDto();

            $docMetaDto->title = $title;
            $docMetaDto->source_url = $link;
            $docMetaDto->download_url = $link;
            $docMetaDto->source_unique_id = 'updates_' . $uniqueId;
            $docMetaDto->work_number = $uniqueId;
            $docMetaDto->language_code = 'spa';
            $docMetaDto->primary_location = '163985';
            $docMetaDto->work_type = 'gazette';

            $link = new UrlFrontierLink(['url' => $link, 'anchor_text' => 'pdf']);
            $link->_metaDto = $docMetaDto;
            $this->arachno->followLink($pageCrawl, $link, true);
        });
    }

    protected function followToDoc(PageCrawl $pageCrawl): void
    {
        $this->handleMeta($pageCrawl);
        $tocTab = $pageCrawl->domCrawler
            ->filterXPath('//*[@id="tabs"]/ul/li/span//a[contains(text(), "Índice")]');

        if ($tocTab->count() > 0) {
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $tocTab->link()->getUri()]));
        }

        $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $pageCrawl->pageUrl->url]));
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

        /** @var DOMElement $docTitle */
        $docTitle = $pageCrawl->domCrawler->filter('.documento-tit')->getNode(0);
        $docTitle = $docTitle->textContent;

        $catalogueDoc->update(['title' => $docTitle]);

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'spa');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '163985');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'act');

        /** @var DOMElement $effDate */
        $effDate = $pageCrawl->domCrawler
            ->filterXPath('//*[@class="conso"]/dt[contains(text(), "Entrada en vigor:")]')
            ->getNode(0);
        $effDate = trim($effDate->nextElementSibling->textContent ?? '');
        $effDate = collect(explode('/', $effDate))->reverse()->join('-');

        try {
            $effectiveDate = Carbon::parse($effDate);
            $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $effectiveDate);
        } catch (InvalidFormatException) {
        }

        if ($pageCrawl->domCrawler->filter('.puntoPDF2 a, .puntoPDF a')->count() > 0) {
            $downloadUrl = $pageCrawl->domCrawler->filter('.puntoPDF2 a, .puntoPDF a')->link()->getUri();
        }
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $downloadUrl ?? null);

        /** @var DOMElement $workDate */
        $workDate = $pageCrawl->domCrawler
            ->filterXPath('//*[@class="conso"]/dt[contains(text(), "Publicado en:")]')
            ->getNode(0);
        $workDate = trim($workDate->nextElementSibling->textContent ?? '');
        $workDate = $workDate ? Str::of($workDate)->after('de') : null;
        $workDate = $workDate ? collect(explode('/', trim($workDate)))->reverse()->join('-') : null;

        try {
            $workDate = Carbon::parse($workDate);
            $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $workDate);
        } catch (InvalidFormatException) {
        }

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);

        return $doc;
    }
}
