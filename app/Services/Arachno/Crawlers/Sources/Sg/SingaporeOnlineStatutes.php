<?php

namespace App\Services\Arachno\Crawlers\Sources\Sg;

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
use DOMDocument;
use DOMElement;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @codeCoverageIgnore
 * slug: sg-sso-agc
 * title: Singapore Statutes Online
 * url: https://sso.agc.gov.sg/
 */
class SingaporeOnlineStatutes extends AbstractCrawlerConfig
{
    /**
     * @return UrlFrontierLink
     */
    protected function handleDate(): UrlFrontierLink
    {
        $date = now()->subDays(1)->format('Ymd');

        return new UrlFrontierLink(['url' => "https://sso.agc.gov.sg/What's-New/New-Legislation/HTML/?Date={$date}"]);
    }

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'sg-sso-agc',
            'throttle_requests' => 700,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    // Current date link
                    //                    new UrlFrontierLink(['url' => "https://sso.agc.gov.sg/What's-New/New-Legislation"]),
                    $this->handleDate(),
                    new UrlFrontierLink([
                        'url' => 'https://sso.agc.gov.sg/Browse/SL-Supp/Published/All?PageSize=20&SortBy=Year&SortOrder=DESC',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://sso.agc.gov.sg/Browse/SL-Supp/Published/All/1?PageSize=20&SortBy=Year&SortOrder=DESC',
                    ]),
                ],
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink(['url' => 'https://sso.agc.gov.sg/Browse/SL/Current/All?PageSize=250&SortBy=Title&SortOrder=ASC']),
                    new UrlFrontierLink(['url' => 'https://sso.agc.gov.sg/Browse/Act/Current/All?PageSize=250&SortBy=Title&SortOrder=ASC']),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/-New\/New-Legislation/')) {
                $this->handleUpdates($pageCrawl);
            }
            if ($this->arachno->matchCSS($pageCrawl, '#colLegis')) {
                $this->getDownloadUrl($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/sso\.agc\.gov\.sg\/Browse\/SL-Supp\/Published\/All/')) {
                $this->handleSlSuppUpdates($pageCrawl);
            }
        }

        if ($this->arachno->matchUrl($pageCrawl, '/Pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/\?WholeDoc=1/')) {
            $this->handleMeta($pageCrawl);
            $this->getPageContent($pageCrawl);
            $this->handleToc($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleUpdates(PageCrawl $pageCrawl): void
    {
        //        $this->arachno->each($pageCrawl, '.whatsnew-section li a',
        $this->arachno->each($pageCrawl, 'div.global-vars[data-json]',
            function (DOMElement $item) use ($pageCrawl) {
                if (str_contains($item->getAttribute('data-json'), '{"List":[{"List":[')) {
                    $json = json_decode($item->getAttribute('data-json'), true);
                    $lists = $json['model']['List'];
                    $docMeta = new DocMetaDto();

                    foreach ($lists as $list) {
                        $lastLists = $list['List'];

                        foreach ($lastLists as $lastList) {
                            $title = $lastList['Title'] ?? '';
                            //                            $type = $lastList['Type'];
                            $url = $lastList['Url'] ?? '';
                            $uniqueId = Str::of($url)->after('sg//')->before('/Published');
                            $docDate = Str::of($url)->after('DocDate=');

                            try {
                                $docDate = Carbon::parse($docDate);
                                $docMeta->work_date = $docDate;
                            } catch (InvalidFormatException) {
                            }

                            $docMeta->title = $title;
                            $docMeta->source_unique_id = "updates_{$uniqueId}";
                            $docMeta->source_url = $url;
                            $docMeta->language_code = 'eng';
                            $docMeta->primary_location = '42441';
                            $docMeta->work_type = 'act';

                            $link = new UrlFrontierLink(['url' => $url]);
                            $link->_metaDto = $docMeta;
                            $this->arachno->followLink($pageCrawl, $link, true);
                        }
                    }
                }
            });
    }

    protected function handleSlSuppUpdates(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.table.browse-list tbody tr', function (DOMElement $tableRow) use ($pageCrawl) {
            $docMeta = new DocMetaDto();
            $crawler = new Crawler($tableRow, $pageCrawl->pageUrl->url, 'https://sso.agc.gov.sg/');

            $link = $crawler->filterXPath('.//td[2]/a')->link()->getUri();
            $date = Str::of($link)->after('DocDate=');

            /** @var DOMElement $anchor */
            $anchor = $crawler->filterXPath('.//td[2]/a')->getNode(0);

            try {
                $date = Carbon::parse($date);
                $docMeta->work_date = $date;
                $docMeta->effective_date = $date;
            } catch (InvalidFormatException) {
            }
            $title = trim($anchor->textContent);
            $workTypes = ['order', 'notification', 'rule', 'regulation'];
            $type = array_intersect(array_map('strtolower', explode(' ', $title)), $workTypes);
            $type = array_values($type)[0] ?? 'regulation';
            $docMeta->title = $title;

            /** @var DOMElement $uniqueId */
            $uniqueId = $crawler->filterXPath('.//td[3]')->getNode(0);
            $uniqueId = trim($uniqueId->textContent);
            $docMeta->source_unique_id = "updates_{$uniqueId}";

            $pdfLink = $crawler->filterXPath('.//td[4]/a')->link()->getUri();
            $docMeta->source_url = $pdfLink;
            $docMeta->download_url = $pdfLink;
            $docMeta->work_number = $uniqueId;
            $docMeta->primary_location = '42441';
            $docMeta->language_code = 'eng';
            $docMeta->work_type = $type;

            $link = new UrlFrontierLink(['url' => $pdfLink]);
            $link->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $link, true);
        });
    }

    protected function getDownloadUrl(PageCrawl $pageCrawl): Doc
    {
        $downloadLink = $pageCrawl->domCrawler->filter('.legis-title a')->link()->getUri();

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $downloadLink);

        $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $downloadLink]), true);

        return $doc;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.table.browse-list tbody tr',
            function (DOMElement $row) use ($pageCrawl) {
                $crawler = new Crawler($row, $pageCrawl->pageUrl->url, 'https://sso.agc.gov.sg');

                /** @var DOMElement $title */
                $title = $crawler->filterXPath('.//td/a')->getNode(0);
                $title = $title->textContent;
                $url = "{$crawler->filterXPath('.//td/a')->link()->getUri()}?WholeDoc=1";
                $downloadLink = $crawler->filterXPath('.//td/a[@class="non-ajax file-download"]')->getNode(0) ? $crawler->filterXPath('.//td/a[@class="non-ajax file-download"]')->link()->getUri() : '';
                $uniqueId = Str::of($url)->after('sg/')->before('?');
                $meta = [
                    'download_url' => $downloadLink,
                ];

                $catDoc = new CatalogueDoc([
                    'title' => $title,
                    'source_unique_id' => $uniqueId,
                    'view_url' => $url,
                    'start_url' => $url,
                    'primary_location_id' => 42441,
                    'language_code' => 'eng',
                ]);

                $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
                CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                    'doc_meta' => $meta,
                ]);
            });

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $this->followNextPage($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function followNextPage(PageCrawl $pageCrawl): void
    {
        $nextBtn = $pageCrawl->domCrawler->filter('.row.pager-row a[aria-label="Next Page"]');

        if ($nextBtn->getNode(0)) {
            $next = $pageCrawl->domCrawler->filter('.row.pager-row a[aria-label="Next Page"]')->link()->getUri();
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $next, 'anchor_text' => 'Next']));
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function getPageContent(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '#legisContent',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//style',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head/link[@rel="stylesheet"]',
            ],
        ]);

        $preStore = function (Crawler $crawler) {
            $tables = $crawler->filter('#legisContent table');
            /** @var DOMElement $table */
            foreach ($tables as $table) {
                $table->setAttribute('style', 'border-color: transparent !important;');
            }
            $tableRows = $crawler->filter('#legisContent table tr');
            /** @var DOMElement $row */
            foreach ($tableRows as $row) {
                $row->setAttribute('style', 'border-color: transparent !important;');
            }
            $tableData = $crawler->filter('#legisContent table td');
            /** @var DOMElement $tdData */
            foreach ($tableData as $tdData) {
                $tdData->setAttribute('style', 'border-color: transparent !important;');
            }

            return $crawler;
        };

        $this->fetchFragmentsContent($pageCrawl);

        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, preStoreCallable: $preStore);
    }

    protected function fetchFragmentsContent(PageCrawl $pageCrawl): void
    {
        $definitions = $this->getFragmentDefinitions($pageCrawl);
        $this->arachno->each($pageCrawl, '#legisContent div[class="dms"]', function (DOMElement $element) use ($definitions) {
            $term = $element->getAttribute('data-term');
            $pageContent = $this->fetchFragment($definitions, $term);
            if (empty($pageContent)) {
                return;
            }

            $domDocument = new DOMDocument();
            try {
                $domDocument->loadHTML("<html><head><meta charset='UTF-8'></head><body>{$pageContent}</body></html>");
            } catch (Exception) {
            }

            /** @var DOMElement $body */
            $body = $domDocument->getElementsByTagName('body')->item(0);

            $current = $element;

            foreach ($body->childNodes->getIterator() as $child) {
                /** @var DOMDocument $domDocument */
                $domDocument = $element->ownerDocument;
                $child = $domDocument->importNode($child, true);
                /** @var DOMElement $current */
                $current->after($child);
                $current = $child;
            }

            $element->remove();
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return array<string,mixed>
     */
    protected function getFragmentDefinitions(PageCrawl $pageCrawl): array
    {
        $definition = [];

        $this->arachno->each($pageCrawl, 'div.global-vars[data-json]', function (DOMElement $item) use (&$definition) {
            if (str_contains($item->getAttribute('data-json'), '"fragments":')) {
                $definition = json_decode($item->getAttribute('data-json'), true);
            }
        });

        return $definition;
    }

    /**
     * @param array<string,mixed> $definitions
     * @param string              $term
     *
     * @return string
     */
    protected function fetchFragment(array $definitions, string $term): string
    {
        if (!$fragment = $definitions['fragments'][$term] ?? false) {
            return '';
        }

        $payload = [
            'TocSysId' => $definitions['tocSysId'],
            'SeriesId' => $term,
            'V' => 36,
            'FragSysId' => $fragment['Item1'],
            '_' => $fragment['Item2'],
        ];

        $response = Http::get('https://sso.agc.gov.sg/Details/GetLazyLoadContent', $payload);

        if ($response->successful()) {
            return $response->body();
        }

        return '';
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleMeta(PageCrawl $pageCrawl): void
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $catalogueDoc->load('docMeta');
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 42441);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->docMeta?->doc_meta['download_url'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'act');
        $url = (string) $catalogueDoc->view_url;
        $workDate = Str::of($url)->after('DocDate=')->before('?WholeDoc');
        try {
            $workDate = Carbon::parse($workDate);
            $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $workDate);
            $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $workDate);
        } catch (InvalidFormatException) {
        }

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleToc(PageCrawl $pageCrawl): void
    {
        $criteria = function ($nodeDTO) {
            $crawler = new Crawler($nodeDTO['node']);
            $href = $crawler->filter('a:not([href*="top"])')->getNode(0);
            $enactText = trim($nodeDTO['node']->textContent);

            return strtolower($nodeDTO['node']->nodeName) === 'a'
                && $href && strtolower($enactText) !== 'enacting formula';
        };

        $this->arachno->captureTocCSS($pageCrawl, '#toc', criteriaFunc: $criteria);
    }
}
