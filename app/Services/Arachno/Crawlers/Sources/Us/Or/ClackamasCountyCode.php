<?php

namespace App\Services\Arachno\Crawlers\Sources\Us\Or;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use DOMElement;
use Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 *
 * slug: us-clackamas
 * title: Clackamas County code
 * url: https://www.clackamas.us
 */
class ClackamasCountyCode extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-clackamas',
            'throttle_requests' => 400,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.clackamas.us/code',
                    ]),
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/clackamas\.us\/code/')) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/([a-z0-9-]+)$/')) {
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
        $this->arachno->each($pageCrawl, '.list_cont .table tr a',
            function (DOMElement $anchor) use ($pageCrawl) {
                $domCrawler = new DomCrawler($anchor);
                if (preg_match('/(Title\s+(?:[6-9]|10))/', $anchor->textContent)) {
                    /** @var DOMElement $element */
                    $element = $domCrawler->filterXPath('.//a')->getNode(0);
                    $title = $element->textContent;

                    $href = $element->getAttribute('href');
                    $uniqueId = Str::of($href)->afterLast('/');

                    $catDoc = new CatalogueDoc([
                        'title' => $title,
                        'source_unique_id' => $uniqueId,
                        'view_url' => $href,
                        'start_url' => $href,
                        'primary_location_id' => '4609',
                        'language_code' => 'eng',
                    ]);

                    $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
                    $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
                }
            });
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
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '4609');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->source_unique_id);

        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'code');

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);

        return $doc;
    }
}
