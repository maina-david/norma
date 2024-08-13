<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 * slug: us-dc-colorado
 * title: Douglas County Colorado State
 * url: https://www.douglas.co.us/
 */
class DouglasCountyColorado extends AbstractCrawlerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/board-county-commissioners\/transparency\/ordinances/')) {
            $this->handleCatalogue($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $this->handleMeta($pageCrawl);
            $pageCrawl->setOcrSettings(['provider' => 'pdfocr', 'languages' => ['eng']]);
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function preFetch(PageCrawl $pageCrawl): void
    {
        $pageCrawl->setProxySettings([
            'provider' => 'scraping_bee',
            'options' => [
                'render_js' => false,
            ],
        ]);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'div.content a[href*="documents"]', function ($elements) use ($pageCrawl) {
            $dom = new DomCrawler($elements->parentNode);
            $href = $elements->getAttribute('href');
            $href = Str::of($href)->replace('.pdf/', '.pdf');
            $title = $dom->filter('a')->getNode(0);
            $title = $title?->textContent;
            $uniqueId = (string) Str::of($href)->afterLast('/')->replace('.pdf', '');
            $catDoc = new CatalogueDoc([
                'title' => $title,
                'start_url' => $href,
                'view_url' => $href,
                'source_unique_id' => $uniqueId,
                'language_code' => 'eng',
                'primary_location_id' => 2661,
            ]);

            $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
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
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 2661);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', (string) 'ordinance');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->start_url);

        return $doc;
    }

    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-dc-colorado',
            'throttle_requests' => 400,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.douglas.co.us/board-county-commissioners/transparency/ordinances',
                    ]),
                ],
            ],
        ];
    }
}
