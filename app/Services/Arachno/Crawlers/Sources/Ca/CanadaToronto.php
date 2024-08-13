<?php

namespace App\Services\Arachno\Crawlers\Sources\Ca;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use DOMElement;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 * slug: ca-toronto
 * title: City of Toronto
 * url: https://www.toronto.ca
 */
class CanadaToronto extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'ca-toronto',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink(['url' => 'https://www.toronto.ca/legdocs/bylaws/lawmcode.htm?1690979322205']),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
        && $this->arachno->matchUrl($pageCrawl, '/\/legdocs\/bylaws\/lawmcode\.htm\?/')) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $this->handleMeta($pageCrawl);
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'td.s2', function (DOMElement $rows) use ($pageCrawl) {
            $domCrawler = new DomCrawler($rows);

            /** @var DOMElement $anchor */
            $anchor = $domCrawler->filter('a')->getNode(0);
            $href = $anchor->getAttribute('href');

            $chapter = $anchor->textContent;
            $uniqueId = Str::of($href)->afterLast('/')->before('.pdf');
            /** @var DOMElement $title */
            $title = $domCrawler->filterXPath('.//td')->getNode(0);
            $title = $title->nextElementSibling?->textContent;

            $catDoc = new CatalogueDoc([
                'title' => $chapter . ' ' . $title,
                'source_unique_id' => $uniqueId,
                'view_url' => $href,
                'start_url' => $href,
                'language_code' => 'eng',
                'primary_location_id' => '2398',
            ]);

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

            $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
        });
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
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '2398');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'bylaw');

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }
}
