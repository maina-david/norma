<?php

namespace App\Services\Arachno\Crawlers\Sources\Nz;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use DOMElement;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 *
 * slug: nz-legislation-gov
 * title: New Zealand Federal Gazette
 * url: https://www.legislation.govt.nz/browse.aspx
 */
class NewZealandLegislationGov extends AbstractCrawlerConfig
{
    private string $baseDomain = 'https://www.legislation.govt.nz';

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/legislation\.govt\.nz\/all\/results\.aspx.*/')) {
                $this->handleMetaForUpdates($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/\/latest\/whole\.html/')) {
                $this->getDownloadLink($pageCrawl);
            }
            //            if ($this->arachno->matchUrl($pageCrawl, '/legislation\.govt\.nz.*\/latest\/whole\.html/')) {
            //                $this->handleCapture($pageCrawl);
            //                $this->handleToc($pageCrawl);
            //            }
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/legislation\.govt\.nz\/all\/results\.aspx.*/')) {
            $this->handleCatalogue($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/legislation\.govt\.nz.*\/latest\/whole\.html/')) {
            $this->handleMeta($pageCrawl);
            $this->handleCapture($pageCrawl);
            $this->handleToc($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleMetaForUpdates(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'a[href*=".html"]', function ($items) use ($pageCrawl) {
            $dom = new DomCrawler($items?->parentNode?->parentNode);
            /** @var DOMElement $href */
            $href = $dom->filter('a[href*=".html"]')->getNode(0);
            $href = $href->getAttribute('href');
            $htmlLink = $this->baseDomain . $href;

            $type = ['bill', 'act'];
            $urlText = explode('/', $htmlLink);
            $workType = array_values(array_intersect($type, $urlText))[0] ?? 'regulation';

            $uniqueId = Str::of($href)->afterLast('/')->before('.html');
            $fullHtmlLink = Str::of($htmlLink)->replace($uniqueId, 'whole') . '#' . $uniqueId;
            $docMeta = new DocMetaDto();
            $docMeta->source_unique_id = 'updates_' . $uniqueId;
            $title = $dom->filter('td.resultsTitle')->getNode(0)?->textContent ?? null;
            $docMeta->title = $title;
            $docMeta->language_code = 'eng';
            $docMeta->primary_location = '98265';
            $docMeta->work_type = $workType;
            $docMeta->source_url = $fullHtmlLink;
            $l = new UrlFrontierLink(['url' => $fullHtmlLink, 'anchor_text' => 'title']);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
        });
        //        $this->arachno->followLinksCSS($pageCrawl, 'div.search-results-pagination ul li:nth-child(13) a[href*="results"]');
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function getDownloadLink(PageCrawl $pageCrawl): void
    {
        $pdfLink = $pageCrawl->domCrawler->filterXPath('//*[@id="ctl00_Cnt_documentNavigationHeader_linkPdfDownload"]')->link()->getUri();

        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $pdfLink);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $pdfLink);

        $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $pdfLink]), true);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'a[href*=".html"]', function ($items) use ($pageCrawl) {
            $dom = new DomCrawler($items->parentNode->parentNode);
            /** @var DOMElement $href */
            $href = $dom->filter('a[href*=".html"]')->getNode(0);
            $href = $href->getAttribute('href');
            $htmlLink = $this->baseDomain . $href;
            $uniqueId = Str::of($href)->afterLast('/')->before('.html');
            $fullHtmlLink = Str::of($htmlLink)->replace($uniqueId, 'whole') . '#' . $uniqueId;
            $title = $dom->filter('td.resultsTitle')->getNode(0)?->textContent ?? '';
            $uniqueId = Str::of($href)->afterLast('/')->before('.html');
            $catDoc = new CatalogueDoc([
                'title' => trim($title),
                'start_url' => $fullHtmlLink,
                'view_url' => $fullHtmlLink,
                'source_unique_id' => $uniqueId,
                'language_code' => 'eng',
                'primary_location_id' => 98265,
            ]);

            $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
        });
        $this->arachno->followLinksCSS($pageCrawl, 'div.search-results-pagination ul li:nth-child(13) a[href*="results"]');
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
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 98265);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', (string) 'law');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'start_url', $catalogueDoc->start_url);

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
                'query' => 'div#legislation',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'head > title',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href,"css/style.css") and @rel="stylesheet"] | //head//link[contains(@href,"css/legislation") and @rel="stylesheet"]',
            ],
        ]);
        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'div#contents',
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
        $criteriaFunc = function ($nodeDTO) {
            $n = $nodeDTO['node'];
            $entryCrawler = new DomCrawler($nodeDTO['node']);
            /** @var DOMElement */
            $emptySpan = $entryCrawler->filter('span')->getNode(0) ?? null;
            $spanParent = $nodeDTO['node']?->parentNode;

            if (($n && str_contains($n->getAttribute('class'), 'label')) || ($emptySpan !== null && str_contains($emptySpan->getAttribute('class'), 'spc'))) {
                return false;
            }

            if (($spanParent && strtolower($spanParent->nodeName) === 'span' || strtolower($spanParent->nodeName) === 'div') && str_contains($spanParent->getAttribute('class'), 'Heading')) {
                return false;
            }

            $href = $nodeDTO['node']->getAttribute('href');

            return strtolower($n->nodeName) === 'a' && ($href !== '') && ($href !== '#');
        };

        $extractLabel = function ($nodeDTO) {
            $entryCrawler = new DomCrawler($nodeDTO['node']);
            $row = $entryCrawler->closest('p')?->getNode(0) ?? null;
            $trParent = $nodeDTO['node']?->parentNode?->parentNode?->parentNode;
            $domCrawler = new DomCrawler($trParent);
            if ($row !== null) {
                return $row->textContent;
            }

            return collect($domCrawler->filter('td')->getIterator())
                ->map(fn ($node) => $node->textContent)
                ->join(' ');
        };
        $extractUniqueId = function ($nodeDTO) {
            $href = $nodeDTO['node']?->getAttribute('href');

            return $href;
        };

        $this->arachno->captureTocCSS(
            $pageCrawl,
            'div#contents',
            criteriaFunc: $criteriaFunc,
            extractLabelFunc: $extractLabel,
            extractUniqueIdFunc: $extractUniqueId,
        );
    }

    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'nz-legislation-gov',
            'throttle_requests' => 200,
            'start_urls' => $this->getCustomCodesStartUrls(),
        ];
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string,array<UrlFrontierLink>>
     */
    protected function getCustomCodesStartUrls(): array
    {
        $year = now()->format('Y');

        return [
            'type_' . CrawlType::FOR_UPDATES->value => [
                new UrlFrontierLink([
                    //                    'url' => "https://www.legislation.govt.nz/all/results.aspx?search=y_act%40regulation%40act%40regulation_{$year}__ac%40rc%40ainf%40anif%40aaif%40rinf%40rnif%40raif_ac%40bn%40rc_25_a&p=1",
                    'url' => "https://www.legislation.govt.nz/all/results.aspx?search=y_act%40bill%40regulation%40act%40bill%40regulation_{$year}__ac%40bc%40rc%40ainf%40anif%40aaif%40bcur%40bena%40rinf%40rnif%40raif_ac%40bc%40rc_25_a&p=1",
                ]),
            ],
            'type_' . CrawlType::FULL_CATALOGUE->value => [
                new UrlFrontierLink([
                    'url' => 'https://www.legislation.govt.nz/all/results.aspx?search=ta_act%40regulation%40act%40regulation_All_ac%40rc%40ainf%40anif%40aaif%40rinf%40rnif%40raif_ac%40bn%40rc_200_a&p=1',
                ]),
            ],
        ];
    }
}
