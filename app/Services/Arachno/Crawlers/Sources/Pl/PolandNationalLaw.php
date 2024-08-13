<?php

namespace App\Services\Arachno\Crawlers\Sources\Pl;

use App\Enums\Arachno\CrawlType;
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
 * slug: pl-poland-national-law
 * title: Poland National Law
 * url: https://dziennikustaw.gov.pl/DU
 */
class PolandNationalLaw extends AbstractCrawlerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) || $this->arachno->crawlIsNewCatalogueWorkDiscovery($pageCrawl)) {
            if ($this->arachno->matchCSS($pageCrawl, 'table#c_table tbody tr td:first-child a[href*="/DU/"]') && !$this->arachno->matchCSS($pageCrawl, 'table#c_table tbody tr a[href*=".pdf"]')) {
                $this->handleIndex($pageCrawl);
                $this->arachno->followLinksCSS($pageCrawl, 'table#c_table tbody tr td:first-child a[href*="/DU/"]');
            }
            if ($this->arachno->matchUrl($pageCrawl, '/(dziennikustaw\.gov\.pl\/DU\/rok\/\d{4}\/(?:[\/a-z]+)?\d+)/')) {
                $this->handleCatalogue($pageCrawl);
            }
        }
        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchCSS($pageCrawl, 'table#c_table tbody tr td:first-child a[href*="/DU/"]') && !$this->arachno->matchCSS($pageCrawl, 'table#c_table tbody tr a[href*=".pdf"]')) {
                $this->handleIndex($pageCrawl);
                $this->arachno->followLinksCSS($pageCrawl, 'table#c_table tbody tr td:first-child a[href*="/DU/"]');
            }
            if ($this->arachno->matchUrl($pageCrawl, '/(dziennikustaw\.gov\.pl\/DU\/rok\/\d{4}\/(?:[\/a-z]+)?\d+)/')) {
                $this->handleUpdatesMeta($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/\.pdf$/')) {
                $this->arachno->capturePDF($pageCrawl);
            }
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/\.pdf$/')) {
                $this->handleMeta($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/\.pdf$/')) {
                $this->arachno->capturePDF($pageCrawl);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function preFetch(PageCrawl $pageCrawl): void
    {
        $pageCrawl->setHttpSettings(['options' => ['verify' => false]]);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleIndex(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'table#c_table tbody tr td:first-child a[href*="/DU/"]', function ($item) {
            $entryCrawler = new DomCrawler($item->parentNode->parentNode);
            /** @var DOMElement */
            $pdfLink = $entryCrawler->filter('td:nth-child(1) a[href*="/DU/"]')->getNode(0);

            $pdfHref = $pdfLink->getAttribute('href');
            $docMeta = new DocMetaDto();
            $docMeta->source_unique_id = $pdfHref;
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'table#c_table tbody tr a[href*=".pdf"]', function ($item) use ($pageCrawl) {
            $entryCrawler = new DomCrawler($item->parentNode->parentNode);
            /** @var DOMElement */
            $pdfLink = $entryCrawler->filter('td a[href*=".pdf"]')->getNode(0);
            /** @var DOMElement */
            $viewLink = $entryCrawler->filter('td:nth-child(1) a[href*="/DU/"]')->getNode(0);
            $pdfHref = $pdfLink->getAttribute('href');
            $viewUrl = $viewLink->getAttribute('href');
            $viewUrl = 'https://dziennikustaw.gov.pl' . $viewUrl;

            $linkHref = Str::of($pdfHref)->afterLast('/');
            $downloadUrl = 'https://dziennikustaw.gov.pl/' . $linkHref;
            $title = $entryCrawler->filter('td:nth-child(2)')->getNode(0)?->textContent ?? '';
            $workNumber = $entryCrawler->filter('td:nth-child(1)')->getNode(0)?->textContent ?? '';
            $date = $entryCrawler->filter('td:nth-child(4)')->getNode(0)?->textContent ?? '';
            $catDoc = new CatalogueDoc([
                'title' => trim($title),
                'start_url' => $downloadUrl,
                'view_url' => $viewUrl,
                'source_unique_id' => str_replace('.pdf', '', $pdfHref),
                'language_code' => 'pol',
                'primary_location_id' => 158667,
            ]);

            $meta = [
                'work_number' => trim($workNumber),
                'work_date' => trim($date),
            ];

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);
        });
        // go to next page of search results
        $this->arachno->followLinksCSS($pageCrawl, 'div.d_prevNext:nth-child(2) > span.frigth.nexPrevYear a');
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
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'pol');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '158667');
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', (string) 'law');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->start_url);

        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catDocMeta->doc_meta['work_number'] ?? null);
        $date = $catDocMeta->doc_meta['work_date'] ?? '';
        if ($date) {
            try {
                $formattedDate = Carbon::parse(trim($date));
                $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $formattedDate);
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
    protected function handleUpdatesMeta(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'table#c_table tbody tr a[href*=".pdf"]', function ($item) use ($pageCrawl) {
            $entryCrawler = new DomCrawler($item->parentNode->parentNode);
            /** @var DOMElement */
            $pdfLink = $entryCrawler->filter('td a[href*=".pdf"]')->getNode(0);
            /** @var DOMElement */
            $viewLink = $entryCrawler->filter('td:nth-child(1) a[href*="/DU/"]')->getNode(0);
            $pdfHref = $pdfLink->getAttribute('href');
            $viewUrl = $viewLink->getAttribute('href');
            $viewUrl = 'https://dziennikustaw.gov.pl' . $viewUrl;

            $linkHref = Str::of($pdfHref)->afterLast('/');
            $downloadUrl = 'https://dziennikustaw.gov.pl/' . $linkHref;
            $title = $entryCrawler->filter('td:nth-child(2)')->getNode(0)?->textContent ?? '';
            $workNumber = $entryCrawler->filter('td:nth-child(1)')->getNode(0)?->textContent ?? '';
            $docMeta = new DocMetaDto();
            $docMeta->source_unique_id = 'updates_' . str_replace('.pdf', '', $pdfHref);
            $docMeta->language_code = 'pol';
            $docMeta->title = $title;
            $docMeta->primary_location = '158667';
            $docMeta->work_type = 'law';
            $docMeta->source_url = $viewUrl;
            $docMeta->download_url = $downloadUrl;
            $docMeta->work_number = $workNumber;
            $date = $entryCrawler->filter('td:nth-child(4)')->getNode(0)?->textContent ?? '';
            if ($date) {
                try {
                    $formattedDate = Carbon::parse($date);
                    $docMeta->work_date = $formattedDate;
                } catch (InvalidFormatException $th) {
                }
            }

            $l = new UrlFrontierLink(['url' => $downloadUrl, 'anchor_tag' => 'PDF']);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'pl-poland-national-law',
            'throttle_requests' => 500,
            'start_urls' => $this->getPolandStartUrls(),
        ];
    }

    /**
     * @return array<string,array<UrlFrontierLink>>
     */
    protected function getPolandStartUrls(): array
    {
        $links = [];

        for ($year = 1918; $year <= now()->format('Y'); $year++) {
            $links[] = new UrlFrontierLink([
                'url' => "https://dziennikustaw.gov.pl/DU/rok/{$year}/1",
            ]);
        }

        return [
            'type_' . CrawlType::FULL_CATALOGUE->value => $links,
            'type_' . CrawlType::FOR_UPDATES->value => [
                new UrlFrontierLink([
                    'url' => 'https://dziennikustaw.gov.pl/DU',
                ]),
            ],
        ];
    }
}
