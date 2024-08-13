<?php

namespace App\Services\Arachno\Crawlers\Sources\Pl;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use DOMElement;
use DOMText;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 *  slug: pl-uw-mazowieck
 *  title: Mazovian Voivodship Provincial Law
 *  url: https://www.gov.pl/
 */
class PolandMazovianVoivodship extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'pl-uw-mazowieck',
            'throttle' => 500,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink(['url' => 'https://www.gov.pl/web/uw-mazowiecki/rozporzadzenia-wojewody-mazowieckiego']),
                    new UrlFrontierLink(['url' => 'https://www.gov.pl/web/uw-mazowiecki/zarzadzenia-wojewody-mazowieckiego-wnp']),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\/uw-mazowiecki/')) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $this->handleMeta($pageCrawl);

            if ($this->arachno->matchUrl($pageCrawl, '/pdf/')) {
                $this->arachno->capturePDF($pageCrawl);
            }
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function getLinks(PageCrawl $pageCrawl): void
    {
        $yearLinks = $pageCrawl->domCrawler->filterXPath('//*[@id="main-content"]/div/ul/li/a');

        /** @var DOMElement $yearLink */
        foreach ($yearLinks as $yearLink) {
            $href = $yearLink->getAttribute('href');

            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => "https://www.gov.pl{$href}"]), true);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->getLinks($pageCrawl);
        $this->arachno->each($pageCrawl, '.article-area__article a', function (DOMElement $list) use ($pageCrawl) {
            $url = $pageCrawl->pageUrl->url;
            $domCrawler = new DomCrawler($list);
            /** @var DOMElement $linkEl */
            $linkEl = $domCrawler->filterXPath('.//a')->getNode(0);
            $link = "https://www.gov.pl{$linkEl->getAttribute('href')}";

            /** @var DOMText $title */
            $title = $domCrawler->filterXPath('.//a/text()[1]')->getNode(0);
            $title = trim($title->textContent);

            $docType = $linkEl->getAttribute('aria-label');
            $docType = Str::of($docType)->afterLast(':');

            /** @var DOMElement|null $uniqueId */
            $uniqueId = $domCrawler->filterXPath('.//a/span[@class="extension"]')->getNode(0) ?? '';
            $uniqueId = $uniqueId ? Str::of($uniqueId->textContent)->before('.') : '';

            $meta['work_number'] = Str::of($link)->after('attachment/');

            if (!str_contains($uniqueId, 'http') && str_contains($docType, 'pdf')) {
                $catDoc = new CatalogueDoc([
                    'title' => $title,
                    'view_url' => $url,
                    'start_url' => $link . '?' . trim($docType),
                    'source_unique_id' => $uniqueId,
                    'primary_location_id' => '159089',
                    'language_code' => 'pol',
                ]);

                $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

                CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                    'doc_meta' => $meta,
                ]);

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
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'pol');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '159089');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->docMeta?->doc_meta['work_number'] ?? null);

        $workType = str_contains((string) $catalogueDoc->view_url, 'Rozporządzenia') ? 'regulation' : 'ordinance';
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $workType);

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);

        return $doc;
    }
}
