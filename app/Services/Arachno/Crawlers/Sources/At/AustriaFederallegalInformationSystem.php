<?php

namespace App\Services\Arachno\Crawlers\Sources\At;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Services\Arachno\Support\DomClosest;
use App\Stores\Corpus\ContentResourceStore;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use DOMNode;
use Http;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @codeCoverageIgnore
 *  slug: at-ris-bka-gv
 *  title: Federal legal information system
 *  url: https://www.ris.bka.gv.at/
 */
class AustriaFederallegalInformationSystem extends AbstractCrawlerConfig
{
    /**
     * @return UrlFrontierLink
     */
    protected function getCurrentUrl(): UrlFrontierLink
    {
        $dateNow = now()->format('d.m.Y');
        $date = now()->subDays(2)->format('d.m.Y');

        return new UrlFrontierLink([
            'url' => "https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=BgblAuth&Titel=&Bgblnummer=&SucheNachGesetzen=False&SucheNachKundmachungen=False&SucheNachVerordnungen=False&SucheNachSonstiges=False&SucheNachTeil1=False&SucheNachTeil2=False&SucheNachTeil3=False&Einbringer=&VonDatum={$date}&BisDatum={$dateNow}&ImRisSeitVonDatum={$date}&ImRisSeitBisDatum={$dateNow}&ImRisSeit=Undefined&ImRisSeitForRemotion=Undefined&ResultPageSize=100&Suchworte=&Position=1&SkipToDocumentPage=true",
        ]);
    }

    /**
     * @return \App\Models\Arachno\UrlFrontierLink[]
     */
    protected function getFullCatalogueUrls(): array
    {
        $dateToday = Carbon::now()->format('d.m.Y');

        return [
            new UrlFrontierLink([
                'url' => "https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Landesnormen&Kundmachungsorgan=&Bundesland=Nieder%c3%b6sterreich&BundeslandDefault=Nieder%c3%b6sterreich&Index=&Titel=&Gesetzesnummer=&VonArtikel=&BisArtikel=&VonParagraf=&BisParagraf=&VonAnlage=&BisAnlage=&Typ=&Kundmachungsnummer=&Unterzeichnungsdatum=&FassungVom={$dateToday}&VonInkrafttretedatum=&BisInkrafttretedatum=&VonAusserkrafttretedatum=&BisAusserkrafttretedatum=&NormabschnittnummerKombination=Und&ImRisSeitVonDatum=&ImRisSeitBisDatum=&ImRisSeit=Undefined&ImRisSeitForRemotion=Undefined&ResultPageSize=100&Suchworte=&Position=1&SkipToDocumentPage=true",
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Index=80%2f05&VonParagraf=0&ResultPageSize=100',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Index=81%2f01&VonParagraf=0&ResultPageSize=100',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Index=82%2f01&VonParagraf=0&ResultPageSize=100',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Index=83*&VonParagraf=0&ResultPageSize=100',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Index=58%2f02&VonParagraf=0&ResultPageSize=100',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Index=60%2f02&VonParagraf=0&ResultPageSize=100',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Index=62*&VonParagraf=0&ResultPageSize=100',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Index=50%2f01&VonParagraf=0&ResultPageSize=100',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Index=93%2f01&VonParagraf=0&ResultPageSize=100',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Index=63%2f04&VonParagraf=0&ResultPageSize=100',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Index=50%2f03&VonParagraf=0&ResultPageSize=100',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Index=82%2f04&VonParagraf=0&ResultPageSize=100',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Index=92*&VonParagraf=0&ResultPageSize=100',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Index=82%2f02&VonParagraf=0&ResultPageSize=100',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Index=95%2f07&VonParagraf=0&ResultPageSize=100',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Index=50%2f02&VonParagraf=0&ResultPageSize=100',
            ]),
            new UrlFrontierLink([
                'url' => 'https://www.ris.bka.gv.at/Ergebnis.wxe?Abfrage=Bundesnormen&Index=60%2f03&VonParagraf=0&ResultPageSize=100',
            ]),
        ];
    }

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'at-ris-bka-gv',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    ...$this->getFullCatalogueUrls(),
                ],
                'type_' . CrawlType::FOR_UPDATES->value => [
                    $this->getCurrentUrl(),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/ris\.bka\.gv\.at\/Ergebnis\.wxe\?Abfrage=Bundesnormen/')
            || $this->arachno->matchUrl($pageCrawl, '/ris\.bka\.gv\.at\/Ergebnis\.wxe\?Abfrage=Landesnormen/')) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/GeltendeFassung\.wxe\?Abfrage=LrNO&Gesetzesnummer=\d+/')
                || $this->arachno->matchUrl($pageCrawl, '/GeltendeFassung\.wxe\?Abfrage=Bundesnormen&Gesetzesnummer=\d+/')) {
                $this->handleMeta($pageCrawl);
                $this->fetchPageContent($pageCrawl);
            }
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            $this->handleUpdates($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleUpdates(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '#MainContent_DocumentsList_TableScrollContainer .bocListDataRow',
            function (DOMElement $element) use ($pageCrawl) {
                $crawler = new Crawler($element, $pageCrawl->pageUrl->url, 'https://www.ris.bka.gv.at');
                $docMeta = new DocMetaDto();

                /** @var DOMElement $title */
                $title = $crawler->filter('.bocListDataCell a.nonWrappingCell')->getNode(0);
                $title = $title->textContent;

                $date = $crawler->filter('.bocListDataCell a.nonWrappingCell')->getNode(0);
                /** @var DOMElement $date */
                $date = $date?->parentNode;
                $date = $date->nextElementSibling;

                $summary = $date?->nextElementSibling;
                $docMeta->summary = trim($summary?->textContent ?? '');

                $pdfLink = $crawler->filter('.bocListDataCell.nativeDocumentLinkIconCell a[href*="pdf"]')->link()->getUri();
                $uniqueId = Str::of($pdfLink)->after('.gv.at/')->before('.');

                $docMeta->title = $title;
                $docMeta->source_unique_id = $uniqueId;
                $docMeta->download_url = $pdfLink;
                $docMeta->source_url = $pdfLink;
                $docMeta->language_code = 'deu';
                $docMeta->primary_location = '42314';
                $docMeta->work_type = 'gazette';
                try {
                    $date = Carbon::parse($date?->textContent);

                    $docMeta->work_date = $date;
                    $docMeta->effective_date = $date;
                } catch (InvalidFormatException) {
                }

                $link = new UrlFrontierLink(['url' => $pdfLink]);
                $link->_metaDto = $docMeta;
                $this->arachno->followLink($pageCrawl, $link, true);
            });
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function followNextLink(PageCrawl $pageCrawl): void
    {
        $nextLink = $pageCrawl->domCrawler->filter('#PagingTopControl_PageNavigation .Next a')->getNode(0);

        if ($nextLink) {
            $link = $pageCrawl->domCrawler->filter('#PagingTopControl_PageNavigation .Next a')->link()->getUri();
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $link, 'anchor_text' => 'Next']));
        }
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '#MainContent_DocumentsList_TableScrollContainer .bocListDataRow',
            function (DOMElement $element) use ($pageCrawl) {
                $crawler = new Crawler($element, $pageCrawl->pageUrl->url, 'https://www.ris.bka.gv.at');
                $meta = [];

                $startUrl = $crawler->filter('.bocListDataCell a.nonWrappingCell')->link()->getUri();
                /** @var DOMElement $title */
                $title = $crawler->filter('.bocListDataCell a.nonWrappingCell')->getNode(0);
                $title = $title->getAttribute('title');

                $url = $crawler->filter('td:last-child a')->link()->getUri();
                $uniqueId = Str::of($startUrl)->after('.gv.at/')->before('?');
                $downloadLink = $crawler->filter('.bocListDataCell.nativeDocumentLinkIconCell a[href$="pdf"]')->link()->getUri();

                $date = $crawler->filter('.bocListDataCell a.nonWrappingCell')->getNode(0);

                try {
                    /** @var DOMElement $date */
                    $date = $date?->parentNode;
                    $date = $date->nextElementSibling;
                    $date = Carbon::parse($date?->textContent);

                    $meta['work_date'] = $date;
                    $meta['effective_date'] = $date;
                } catch (InvalidFormatException) {
                }

                $workNumber = Str::of($uniqueId)->afterLast('/');
                $summary = $crawler->filter('.bocListDataCell .nativeDocumentLinkCell')->getNode(0);
                /** @var DOMElement $summary */
                $summary = $summary?->parentNode;
                $summary = $summary->previousElementSibling;

                $meta['work_number'] = $workNumber;
                $meta['download_url'] = $downloadLink;

                $catDoc = new CatalogueDoc([
                    'title' => trim($title),
                    'source_unique_id' => $uniqueId,
                    'start_url' => "{$url}&{$uniqueId}",
                    'view_url' => $url,
                    'primary_location_id' => 42314,
                    'language_code' => 'deu',
                    'summary' => $summary?->textContent,
                ]);

                $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
                CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                    'doc_meta' => $meta,
                ]);
            });

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $this->followNextLink($pageCrawl);
        }
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleMeta(PageCrawl $pageCrawl): void
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $catalogueDoc->load('docMeta');

        $title = $pageCrawl->domCrawler->filter('#MainContent_DocumentRepeater_BundesnormenCompleteNormDocumentData_0_LangtitelContainer_0')->getNode(0)
            ?? $pageCrawl->domCrawler->filter('#MainContent_DocumentRepeater_LandesnormenCompleteNormDocumentData_0_LangtitelContainer_0')->getNode(0);
        $title = preg_replace('/^(?:Langtitel)\r+\n+\s+/', '', trim($title?->textContent ?? ''));

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'deu');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 42314);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->docMeta?->doc_meta['download_url'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->docMeta?->doc_meta['work_number'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'summary', $catalogueDoc->summary);
        $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $catalogueDoc->docMeta?->doc_meta['effective_date'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $catalogueDoc->docMeta?->doc_meta['work_date'] ?? null);

        $catalogueDoc->update(['title' => $title]);

        /** @var string $title */
        $workType = str_starts_with($title, 'Verordnung') ? 'ordinance' : 'law';

        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $workType);

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function fetchPageContent(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '#content',
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
        //        $removeQueries = $this->arrayOfQueries([
        //            [
        //                'type' => DomQueryType::CSS,
        //                'query' => '#MainContent_DocumentRepeater_BundesnormenCompleteNormDocumentData_0_LangtitelContainer_0',
        //            ],
        //        ]);

        $preStore = function (Crawler $crawler) {
            $externalLinks = $crawler->filter('a:not([href*="pdf"])');

            /** @var DOMElement $externalLink */
            foreach ($externalLinks as $externalLink) {
                $externalLink->removeAttribute('href');
            }

            $pdfIcons = $crawler->filter('a.iconOnlyLink img');

            /** @var DOMElement $pdfIcon */
            foreach ($pdfIcons as $pdfIcon) {
                $pdfIcon->removeAttribute('style');
                $pdfIcon->setAttribute('style', 'height: 50px;');
            }

            $toc = $crawler->filter('.contentBlock .InhaltUeberschrift, .contentBlock .InhaltUeberschriftWithinTable.AlignCenter')->getNode(0);

            /** @var DOMElement|null $tocTable */
            $tocTable = $crawler->filter('#Tabelle1, .tableOfContent')->getNode(0);
            /** @var DOMElement|null $toc */
            if ($tocTable) {
                $tocTable->remove();
                $toc?->remove();
            }

            $closest = app(DomClosest::class);
            if ($toc) {
                /** @var DOMNode $toc */
                /** @var DOMElement|null $table */
                $table = $closest->closest($toc, 'table')?->getNode(0);
                //            $tocContent = $toc?->parentNode;
                $table?->remove();
            }

            /** @var DOMElement|null $text */
            $text = $crawler->filter('h3#MainContent_DocumentRepeater_LandesnormenCompleteNormDocumentData_1_HeaderTextField_1')->getNode(0);

            if ($text && str_contains(trim($text->textContent), 'Text')) {
                $text->remove();
            }

            $hiddenSec = $crawler->filter('h2.onlyScreenreader');

            /** @var DOMElement $hidden */
            foreach ($hiddenSec as $hidden) {
                $hidden->remove();
            }

            $removableText = $crawler->filter('h3.onlyScreenreader');

            /** @var DOMElement $text */
            foreach ($removableText as $text) {
                if (str_contains($text->textContent, 'Text')) {
                    $text->remove();
                }
            }

            if ($crawler->filter('a[href*="pdf"]')->count() > 0) {
                foreach ($crawler->filter('a[href*="pdf"]') as $pdfLink) {
                    /** @var DOMElement $pdfLink */
                    $fullUrl = str_contains($pdfLink->getAttribute('href'), 'https://www.ris.bka.gv.at')
                    || str_starts_with($pdfLink->getAttribute('href'), 'http')
                        ? $pdfLink->getAttribute('href')
                        : "https://www.ris.bka.gv.at{$pdfLink->getAttribute('href')}";

                    $pLink = retry(4, function () use ($fullUrl) {
                        return Http::timeout(3000)->get($fullUrl);
                    }, 3000);
                    $contentResourceStore = app(ContentResourceStore::class);

                    $resource = $contentResourceStore->storeResource($pLink, 'application/pdf');
                    $newHref = $contentResourceStore->getLinkForResource($resource);
                    $pdfLink->setAttribute('href', $newHref);
                }
            }

            return $crawler;
        };

        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, preStoreCallable: $preStore);
    }
}
