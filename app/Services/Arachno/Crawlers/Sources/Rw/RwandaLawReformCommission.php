<?php

namespace App\Services\Arachno\Crawlers\Sources\Rw;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 * slug: rw-rlrc-gov
 * title: Rwanda Law Reform Commission
 * url: https://www.rlrc.gov.rw
 */
class RwandaLawReformCommission extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'rw-rlrc-gov',
            'throttle_requests' => 400,
            'verify_ssl' => false,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.rlrc.gov.rw/mandate/laws-of-rwanda?tx_filelist_filelist%5Baction%5D=list&tx_filelist_filelist%5Bcontroller%5D=File&tx_filelist_filelist%5Bpath%5D=%2Fuser_upload%2FRLRC%2FLaw_of_Rwanda%2F&cHash=20343fdc04279b47835d951d5dbf6da3',
                    ]),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/%2Fuser_upload%2FRLRC%2FLaw_of_Rwanda%/')) {
            if ($this->arachno->matchCSS($pageCrawl, '.table tr td a > img[src*="folder_thumbnail"]')) {
                $this->handleLinks($pageCrawl);
            }
            if ($this->arachno->matchCSS($pageCrawl, '.table td a > img[src*=pdf]')) {
                $this->handleCatalogue($pageCrawl);
            }
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/&token=/')) {
            $this->handleMeta($pageCrawl);
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleLinks(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.table td:last-child a',
            function (DOMElement $elem) use ($pageCrawl) {
                $domCrawler = new DomCrawler($elem, $pageCrawl->pageUrl->url, 'https://www.rlrc.gov.rw');

                $link = $domCrawler->filterXPath('.//a')->link()->getUri();

                $frontierLink = new UrlFrontierLink(['url' => $link]);

                $this->arachno->followLink($pageCrawl, $frontierLink, true);
            });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.table tbody > tr',
            function (DOMElement $elem) use ($pageCrawl) {
                $domCrawler = new DomCrawler($elem, $pageCrawl->pageUrl->url, 'https://www.rlrc.gov.rw');

                /** @var DOMElement $anchorElem */
                $anchorElem = $domCrawler->filterXPath('.//td[2]/a')->getNode(0);

                if ($anchorElem->textContent !== '..' && str_contains($anchorElem->getAttribute('href'), 'token=')) {
                    $relAnchorElem = $domCrawler->filterXPath('.//td[2]/a');
                    $link = $relAnchorElem->link()->getUri();

                    /** @var DOMElement $title */
                    $title = $relAnchorElem->getNode(0);
                    $title = $title->textContent;
                    $title = Str::of(trim($title))->before('.pdf');
                    $title = explode('_', $title);
                    $title = implode(' ', $title);

                    /** @var DOMElement $effectiveDateElem */
                    $effectiveDateElem = $domCrawler->filterXPath('.//tr/td[4]')->getNode(0);
                    $effectiveDate = trim($effectiveDateElem->textContent);

                    try {
                        $effectiveDate = Carbon::parse($effectiveDate);
                    } catch (InvalidFormatException $th) {
                    }

                    $uniqueId = Str::of($link)->after('token=');
                    $meta = [
                        'work_number' => $uniqueId,
                        'download_url' => $link,
                        'effective_date' => $effectiveDate,
                    ];

                    $catDoc = new CatalogueDoc([
                        'title' => $title,
                        'source_unique_id' => $uniqueId,
                        'primary_location_id' => '31',
                        'start_url' => $link,
                        'view_url' => $link,
                        'language_code' => 'eng',
                    ]);

                    $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

                    CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                        'doc_meta' => $meta,
                    ]);
                    $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
                }
            });

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $this->nextPage($pageCrawl);
        }
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
        $catalogueDoc->load('docMeta');
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '31');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $catalogueDoc->docMeta?->doc_meta['effective_date'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $catalogueDoc->docMeta?->doc_meta['effective_date'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'law');

        return $doc;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function nextPage(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.f3-widget-paginator li.next a',
            function (DOMElement $elem) use ($pageCrawl) {
                $domCrawler = new DomCrawler($elem, $pageCrawl->pageUrl->url, 'https://www.rlrc.gov.rw');

                $nextLink = $domCrawler->filterXPath('.//a')->link()->getUri();

                if ($domCrawler->filterXPath('.//a')->getNode(0)) {
                    $this->arachno->followLink($pageCrawl,
                        new UrlFrontierLink(['url' => $nextLink, 'anchor_text' => 'Next']));
                }
            });
    }
}
