<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use DOMElement;

/**
 * @codeCoverageIgnore
 *
 * slug: us-federal-register
 * title: USA Federal Register
 * url: https://www.federalregister.gov
 */
class FederalRegister extends AbstractCrawlerConfig
{
    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/federalregister\.gov\/documents\/current/')) {
            $this->arachno->followLinksCSS($pageCrawl, '.doc .toc-documents a.cj-fancy-tooltip', true);
        }
        if ($this->arachno->matchCSS($pageCrawl, '.content-block #fulltext_content_area')) {
            $this->handleMeta($pageCrawl);
            $this->arachno->followLinksXpath($pageCrawl, '//*[@id="main"]/div[4]/div/div/div[2]/div[1]/div[1]/div/div[2]/dl/dd[1]/a', true);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    //    protected function handleCapture(PageCrawl $pageCrawl): void
    //    {
    //        $bodyQueries = [new DomQuery([
    //            'type' => DomQueryType::CSS,
    //            'query' => '#fulltext_content_area',
    //        ])];
    //        $headQueries = $this->arrayOfQueries([
    //            [
    //                'type' => DomQueryType::CSS,
    //                'query' => 'head > title',
    //            ],
    //            [
    //                'type' => DomQueryType::CSS,
    //                'query' => 'head > meta[name="title"]',
    //            ],
    //            [
    //                'type' => DomQueryType::CSS,
    //                'query' => 'head > meta[name="description"]',
    //            ],
    //            [
    //                'type' => DomQueryType::CSS,
    //                'query' => 'head > meta[property="article:published_time"]',
    //            ],
    //            [
    //                'type' => DomQueryType::XPATH,
    //                'query' => '//head//link[@rel="stylesheet" and @media="all"]',
    //            ],
    //        ]);
    //        $removeQueries = $this->arrayOfQueries([
    //            [
    //                'type' => DomQueryType::CSS,
    //                'query' => '.hidden-print, .icon-fr2, .cj-fancy-tooltip',
    //            ],
    //            [
    //                'type' => DomQueryType::CSS,
    //                'query' => '.printed-page, .printed-page-wrapper, .unprinted-element-wrapper',
    //            ],
    //        ]);
    //        $postProcessCss = fn (string $css) => str_replace('.bootstrap-scope ', '', $css);
    //        $this->arachno->capture(
    //            $pageCrawl,
    //            $bodyQueries,
    //            $headQueries,
    //            $removeQueries,
    //            $postProcessCss
    //        );
    //    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc
     */
    protected function handleMeta(PageCrawl $pageCrawl): Doc
    {
        $downloadUrl = $pageCrawl->domCrawler->filterXPath('//*[@id="main"]/div[4]/div/div/div[2]/div[1]/div[1]/div/div[2]/dl/dd[1]/a')
            ->link()
            ->getUri();

        $doc = $this->arachno->setDocMetaPropertyByCSS(
            $pageCrawl, 'source_unique_id', 'div.row.doc.doc-document.doc-published > div.col-md-3.doc-aside.doc-details > div:nth-child(1) > div > div.content-block > dl > dd.doc_number');
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaPropertyByXpath($pageCrawl, 'title', '//head//meta[@name="title"]//@content');
        $this->arachno->setDocMetaPropertyByCSS($pageCrawl, 'work_type', '#main > div.main-title-bar > h1');
        $this->arachno->setDocMetaPropertyByCSS(
            $pageCrawl, 'work_number', 'div.row.doc.doc-document.doc-published > div.col-md-3.doc-aside.doc-details > div:nth-child(1) > div > div.content-block > dl > dd.doc_number');
        $this->arachno->setDocMetaPropertyByXpath(
            $pageCrawl, 'work_date', '//head//meta[@property="article:published_time"]//@content');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $pageCrawl->getFinalRedirectedUrl());
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $downloadUrl);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '384');

        $summaryTitle = $pageCrawl->domCrawler->filter('#h-3')->getNode(0);
        $summaryTitle = $summaryTitle ? strtolower($summaryTitle->textContent) : null;

        if ($summaryTitle === 'summary:') {
            /** @var DOMElement $summary */
            $summary = $pageCrawl->domCrawler->filter('#h-3')->getNode(0);
            $summary = $summary->nextElementSibling ? trim($summary->nextElementSibling->textContent) : null;

            $this->arachno->setDocMetaProperty($pageCrawl, 'summary', $summary);
        }

        $doc->source_unique_id = "updates_{$doc->source_unique_id}";

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
            'for_update' => true,
            'slug' => 'us-federal-register',
            'throttle_requests' => 100,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.federalregister.gov/documents/current',
                    ]),
                ],
            ],
        ];
    }
}
