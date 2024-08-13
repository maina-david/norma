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

/**
 * @codeCoverageIgnore
 * slug: pl-poland-isap
 * title: Poland Isap
 * url: https://isap.sejm.gov.pl/isap.nsf/ByYear.xsp?type=WDU
 */
class PolandIsap extends AbstractCrawlerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/api\.sejm\.gov\.pl\/eli\/acts\/DU\/\d{4}/')
            && !$this->arachno->matchUrl($pageCrawl, '/eli\/acts\/DU\/\d{4}\/\d+/')) {
            $this->followPositionLink($pageCrawl);
        }

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/eli\/acts\/DU\/\d{4}\/\d+/')) {
            $this->handleCatalogue($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/api\.sejm\.gov\.pl\/eli\/acts\/DU\/\d{4}/')) {
                $this->followStartUrl($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/isap\.nsf\/download\.xsp\/[0-9A-Z]+\/[A-Z]\/[0-9A-Za-z]+\.pdf/')) {
                $this->handleMeta($pageCrawl);
                $this->arachno->capturePDF($pageCrawl);
            }
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/api\.sejm\.gov\.pl\/eli\/acts\/MP\/\d{4}/')) {
                $this->followStartUrl($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/isap\.sejm\.gov\.pl\/isap\.nsf\/DocDetails\.xsp\?id=[0-9A-Za-z]+/')) {
                $this->handleMetaForUpdates($pageCrawl);
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
        $pageCrawl->setProxySettings([
            'provider' => 'scraping_bee',
            'options' => ['render_js' => false],
        ]);
    }

    protected function followPositionLink(PageCrawl $pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $items = $json['items'];

        foreach ($items as $item) {
            $this->arachno->followLink(
                $pageCrawl,
                new UrlFrontierLink([
                    'url' => "https://api.sejm.gov.pl/eli/acts/DU/{$item['year']}/{$item['pos']}",
                ]));
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return array<string, mixed>
     */
    protected function getConsolidatedLink(PageCrawl $pageCrawl): array
    {
        $json = $pageCrawl->getJson();

        $texts = collect($json['texts']);
        $docId = $json['address'];
        $title = $json['title'];
        //        $effectiveDate = $json['entryIntoForce'];

        $type = $texts->where('type', 'O')->first();
        $typeU = $texts->where('type', 'U')->first();
        if (isset($json['repealDate']) && !$type && !$typeU) {
            return [];
        }
        $path = "https://isap.sejm.gov.pl/isap.nsf/download.xsp/{$docId}/O/{$type['fileName']}";

        if ($typeU = $texts->where('type', 'U')->first()) {
            $path = "https://isap.sejm.gov.pl/isap.nsf/download.xsp/{$docId}/U/{$typeU['fileName']}";
        }

        return [
            'title' => $title,
            'source_unique_id' => $docId,
            'start_url' => $path,
            'view_url' => "https://isap.sejm.gov.pl/isap.nsf/DocDetails.xsp?id={$docId}",
            'language_code' => 'pol',
            'primary_location_id' => 158667,
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $meta = $this->getConsolidatedLink($pageCrawl);
        if (empty($meta)) {
            return;
        }
        $docMeta = [
            'work_number' => $meta['source_unique_id'],
            'download_url' => $meta['start_url'],
        ];

        $catDoc = new CatalogueDoc($meta);

        $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
        CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
            'doc_meta' => $docMeta,
        ]);
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
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'pol');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '158667');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'law');
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->source_unique_id);

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);

        return $doc;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleMetaForUpdates(PageCrawl $pageCrawl): void
    {
        $docMeta = new DocMetaDto();
        $dom = $pageCrawl->domCrawler;
        /** @var DOMElement */
        $consolidatedPdfLink = $dom->filter('div > div:nth-child(6) > div.col-sm-8 a')->getNode(0) ?? null;
        /** @var DOMElement */
        $announcementPdfLink = $dom->filter('div > div:nth-child(4) > div.col-sm-8 a')->getNode(0) ?? null;
        $pdfHref = '';
        if ($consolidatedPdfLink !== null && str_contains($consolidatedPdfLink->getAttribute('href'), '.pdf')) {
            $pdfHref = $consolidatedPdfLink->getAttribute('href');
        } elseif ($announcementPdfLink !== null && str_contains($announcementPdfLink->getAttribute('href'), '.pdf')) {
            $pdfHref = $announcementPdfLink->getAttribute('href');
        }

        $viewLink = $pageCrawl->pageUrl->url;
        $uniqueId = Str::of($pdfHref)->afterLast('/')->replace('.pdf', '');

        $downloadUrl = 'https://isap.sejm.gov.pl' . $pdfHref;
        $title = $dom->filter('div.doc-details h2')->getNode(0)?->textContent ?? '';
        $workNumber = $dom->filter('div.doc-details h1')->getNode(0)?->textContent ?? '';
        $announcementDate = $dom->filter('div.doc-details div:nth-child(6).row div:nth-child(2).col-sm-8')->getNode(0)?->textContent ?? '';
        $announcementDate2 = $dom->filter('div.doc-details div:nth-child(8).row div:nth-child(2).col-sm-8')->getNode(0)?->textContent ?? '';
        $date = preg_match('/\d{4}-\d{1,2}-\d{1,2}/', trim($announcementDate)) ? $announcementDate : $announcementDate2;

        $docMeta->source_unique_id = $uniqueId;
        $docMeta->title = $title;
        $docMeta->language_code = 'pol';
        $docMeta->primary_location = '158667';
        $docMeta->work_type = 'law';
        $docMeta->work_number = $workNumber;
        $docMeta->source_url = $viewLink;
        $docMeta->download_url = $downloadUrl;
        $date = $date;
        if ($date) {
            try {
                /** @var Carbon */
                $workDate = Carbon::createFromFormat('j M Y', trim($date));
                $docMeta->work_date = $workDate;
            } catch (InvalidFormatException $th) {
            }
        }
        $l = new UrlFrontierLink(['url' => $downloadUrl, 'anchor_text' => 'PDF']);
        $l->_metaDto = $docMeta;
        $this->arachno->followLink($pageCrawl, $l, true);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function followStartUrl($pageCrawl): void
    {
        $json = $pageCrawl->getJson();
        $items = $json['items'];
        // Loop through the array of items and follow urls
        foreach ($items as $item) {
            $startUrl = "https://isap.sejm.gov.pl/isap.nsf/DocDetails.xsp?id={$item['address']}";
            $l = new UrlFrontierLink(['url' => $startUrl]);
            $docMeta = new DocMetaDto();

            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'pl-poland-isap',
            'throttle_requests' => 500,
            'start_urls' => $this->getPolandStartUrls(),
        ];
    }

    /**
     * @return array<string,array<UrlFrontierLink>>
     */
    protected function getPolandStartUrls(): array
    {
        $fullCatalogueLinks = [];
        $updateLinks = [];

        for ($year = 1918; $year <= now()->format('Y'); $year++) {
            $fullCatalogueLinks[] = new UrlFrontierLink([
                'url' => "http://api.sejm.gov.pl/eli/acts/DU/{$year}",
            ]);
        }
        for ($year = 1930; $year <= now()->format('Y'); $year++) {
            $updateLinks[] = new UrlFrontierLink([
                'url' => "http://api.sejm.gov.pl/eli/acts/MP/{$year}",
            ]);
        }

        return [
            'type_' . CrawlType::FULL_CATALOGUE->value => $fullCatalogueLinks,
            'type_' . CrawlType::FOR_UPDATES->value => $updateLinks,
        ];
    }
}
