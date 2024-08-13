<?php

namespace App\Services\Arachno\Crawlers\Sources\Ie;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Throwable;

/**
 * @codeCoverageIgnore
 * slug: ie-irish-statute-book
 * title: Ireland - Irish Statute Book
 * url: https://www.irishstatutebook.ie/
 */
class IrishStatuteBook extends AbstractCrawlerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $this->handleCatalogue($pageCrawl);
        }
        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/(\/si$)|(acts.html)/')) {
            $this->handleUpdates($pageCrawl);
        }
        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\.pdf$/')) {
            $this->handleUpdatesPdf($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $pageCrawl->isCatalogueStartUrl()) {
            $this->handleCoverPage($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.datatable tr a[href*=".html"]', function ($entry) use ($pageCrawl) {
            // $entryCrawler = new DomCrawler($entry);
            $id = $entry->getAttribute('href');
            $id = str_replace('.html', '', $id);
            $redirectedUrl = $pageCrawl->getFinalRedirectedUrl();
            $year = (string) Str::of($redirectedUrl)->after('/eli/')->before('/');
            $parts = explode('/', $id);
            $number = 1;
            foreach ($parts as $part) {
                if (is_numeric($part) && $part !== $year) {
                    $number = (int) $part;
                }
            }
            if (str_contains($redirectedUrl, '/act')) {
                $id = '/' . $year . '/' . $parts[1] . '/' . $number;
            } else {
                $id = '/' . $year . '/' . $parts[3] . '/' . $number;
            }

            $title = $entry->textContent;
            $url = $entry?->getAttribute('href') ?? '';
            if (!$url) {
                return;
            }
            $startUrl = str_contains($id, '/si/') ? 'https://www.irishstatutebook.ie' . $url : 'https://www.irishstatutebook.ie/eli/' . $year . '/' . $url;
            $catDoc = new CatalogueDoc([
                'title' => trim($title),
                'start_url' => str_replace('/index.html', '/print.html', $startUrl),
                'view_url' => $startUrl,
                'source_unique_id' => (string) $id,
                'language_code' => 'eng',
                'primary_location_id' => 42312,
            ]);

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

            if (str_contains($id, '/si/')) {
                $cats = ['Ireland - Statutory Instruments'];
            } else {
                $cats = ['Ireland - Acts of the Oireachtas'];
            }
            $this->arachno->addCategoriesToCatalogueDoc($catalogueDoc, $cats);
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleUpdates(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.datatable tr', function ($entry) use ($pageCrawl) {
            $entryCrawler = new DomCrawler($entry);
            /** @var DOMElement|null */
            $pdfLink = $entryCrawler->filter('a[href*=".pdf"]')->getNode(0);

            if (!$pdfLink) {
                return;
            }

            $pdfHref = $pdfLink->getAttribute('href');
            /** @var DOMElement|null */
            $link = $entryCrawler->filter('a[href*=".html"]')->getNode(0);

            $docMeta = new DocMetaDto();
            $docMeta->source_unique_id = (string) Str::of($pdfHref)->after('/pdf/')->replace('.pdf', '')->replace('.', '-');
            $docMeta->title = $entryCrawler->filter('td:nth-child(2)')->getNode(0)?->textContent;
            $docMeta->language_code = 'eng';
            $docMeta->primary_location = '42312';
            $docMeta->work_type = $this->arachno->matchUrl($pageCrawl, '/acts\.html$/') ? 'act' : 'statutory_instrument';
            $docMeta->work_number = $entryCrawler->filter('td')->getNode(0)?->textContent ?? null;
            $docMeta->download_url = 'https://www.irishstatutebook.ie' . str_replace('../', 'eli/', $pdfHref);
            $docMeta->source_url = $link?->getAttribute('href')
                ? 'https://www.irishstatutebook.ie' . $link->getAttribute('href')
                : $docMeta->download_url;

            $l = new UrlFrontierLink(['url' => $pdfHref, 'anchor_text' => 'PDF']);
            $l->_metaDto = $docMeta;
            $frontierLink = $this->arachno->followLink($pageCrawl, $l, true);
            // only go fetch other meta data if it's a new link we haven't crawled
            if ($frontierLink && $link) {
                $docMeta = new DocMetaDto();
                /** @var DomCrawler */
                $domCrawler = $this->arachno->fetchLink($pageCrawl, $link->getAttribute('href'));
                /** @var DOMElement|null */
                $description = $domCrawler->filter('meta[property="eli:description"]')->getNode(0);
                if ($description) {
                    $docMeta->summary = trim($description->getAttribute('content'));
                }
                /** @var DOMElement|null */
                $date = $domCrawler->filter('meta[property="eli:date_document"]')->getNode(0);
                if ($date) {
                    try {
                        $date = Carbon::parse($date->getAttribute('content'));
                        $docMeta->work_date = $date;
                    } catch (Throwable $th) {
                    }
                }
                $this->arachno->addMetaToLink($frontierLink, $docMeta);
            }
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleUpdatesPdf(PageCrawl $pageCrawl): void
    {
        $this->arachno->capturePDF($pageCrawl);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCoverPage(PageCrawl $pageCrawl): void
    {
        /** @var CatalogueDoc */
        $catDoc = $pageCrawl->pageUrl->catalogueDoc;
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaPropertyByCSS($pageCrawl, 'work_date', 'meta[property="eli:date_document"]');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catDoc->title);
        $this->arachno->setDocMetaPropertyByCSS($pageCrawl, 'work_number', 'meta[property="eli:number"]');
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '42312');

        $this->handlePageCapture($pageCrawl);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function handlePageCapture(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '.act-content',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href,"main") and @rel="stylesheet"]',
            ],
        ]);
        $preStore = function ($dom) {
            /** @var DOMElement */
            $outerTable = $dom->filter('table')->getNode(0);
            $outerTable->setAttribute('id', 'outerTable');

            return $dom;
        };
        $this->arachno->capture(
            $pageCrawl,
            $bodyQueries,
            $headQueries,
            [],
            null,
            $preStore,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'ie-irish-statute-book',
            'start_urls' => $this->getIrishStartUrls(),
            'throttle_requests' => 100,
            'css' => '#outerTable, #outerTable > tbody > tr, #outerTable > tbody > tr > td { border-width: 0px;}',
        ];
    }

    /**
     * @return array<string,array<UrlFrontierLink>>
     */
    protected function getIrishStartUrls(): array
    {
        $fullOut = [];

        for ($year = 1922; $year <= now()->format('Y'); $year++) {
            $fullOut[] = new UrlFrontierLink([
                'url' => "https://www.irishstatutebook.ie/eli/{$year}/act",
            ]);
            $fullOut[] = new UrlFrontierLink([
                'url' => "https://www.irishstatutebook.ie/eli/{$year}/si",
            ]);
        }

        return [
            'type_' . CrawlType::FULL_CATALOGUE->value => $fullOut,
            'type_' . CrawlType::FOR_UPDATES->value => [
                new UrlFrontierLink([
                    'url' => 'https://www.irishstatutebook.ie/eli/' . now()->format('Y') . '/si',
                ]),
                new UrlFrontierLink([
                    'url' => 'https://www.irishstatutebook.ie/eli/acts.html',
                ]),
            ],
        ];
    }
}
