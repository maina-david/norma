<?php

namespace App\Services\Arachno\Crawlers\Sources\De;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
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
 *
 * slug: de-verkuendung-bayern
 * title: verkuendung-bayern.de
 * url: https://www.verkuendung-bayern.de
 */
class BavariaProvinceVerkuendung extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'de-verkuendung-bayern',
            'throttle_requests' => 500,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink(['url' => 'https://www.verkuendung-bayern.de/gvbl/']),
                    new UrlFrontierLink(['url' => 'https://www.verkuendung-bayern.de/gvbl/?offset=16']),
                    new UrlFrontierLink(['url' => 'https://www.verkuendung-bayern.de/baymbl']),
                    new UrlFrontierLink(['url' => 'https://www.verkuendung-bayern.de/baymbl/?offset=16']),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl)
            && !$this->arachno->matchUrl($pageCrawl, '/(\d+-\d+\/?$|.pdf)/')) {
            $this->handleUpdates($pageCrawl);
        }

        if ($this->arachno->matchCSS($pageCrawl, 'div.text_html')) {
            if ($this->arachno->matchXpath($pageCrawl, '//*[@id="head-left"]/div/div/div/a')) {
                $this->getDownloadLink($pageCrawl);
            } else {
                $this->getHtmlContent($pageCrawl);
            }
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleUpdates(PageCrawl $pageCrawl): void
    {
        $this->arachno->eachX($pageCrawl, '//*[@id="search-results-table"]/tbody/tr',
            function (DOMElement $rows) use ($pageCrawl) {
                $domCrawler = new DomCrawler($rows);

                /** @var DOMElement $linkElement */
                $linkElement = $domCrawler->filterXPath('.//td/a')->getNode(0);

                $href = $linkElement->getAttribute('href');

                /** @var DOMElement $workDate */
                $workDate = $domCrawler->filterXPath('.//td[@data-label="Verkündung"]')->getNode(0);
                $workDate = trim($workDate->textContent);

                /** @var DOMElement $title */
                $title = $domCrawler->filterXPath('.//td[@data-label="Titel"]')->getNode(0);
                $title = trim($title->textContent);

                $docMetaDto = new DocMetaDto();

                $docMetaDto->title = $title;
                $docMetaDto->source_url = "https://www.verkuendung-bayern.de{$href}";
                $docMetaDto->work_type = 'regulation';
                $docMetaDto->primary_location = '108495';
                $docMetaDto->language_code = 'deu';

                try {
                    $docMetaDto->work_date = Carbon::parse($workDate);
                } catch (InvalidFormatException) {
                }

                $link = new UrlFrontierLink(['url' => $docMetaDto['source_url']]);
                $link->_metaDto = $docMetaDto;
                $this->arachno->followLink($pageCrawl, $link, true);
            });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    //    protected function followNextLink(PageCrawl $pageCrawl): void
    //    {
    //        /** @var string $path */
    //        $path = $pageCrawl->domCrawler->getUri();
    //        $path = parse_url($path, PHP_URL_PATH);
    //
    //        $this->arachno->eachX($pageCrawl, '//*[@id="page-browser"]/nav/ul/li/a[@title="Weiterblättern"]', function (DOMElement $el) use ($pageCrawl, $path) {
    //            $nextLink = $this->baseLink . $path . $el->getAttribute('href');
    //
    //            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $nextLink, 'anchor_text' => 'Next']));
    //        });
    //    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function getDownloadLink(PageCrawl $pageCrawl): void
    {
        $url = $pageCrawl->getFinalRedirectedUrl();

        $domCrawler = $pageCrawl->domCrawler;

        /** @var DOMElement|null $pdfLinkEl */
        $pdfLinkEl = $domCrawler->filterXPath('//*[@id="head-left"]/div/div/div/a')->getNode(0);
        $pdfHref = $pdfLinkEl?->getAttribute('href') ?? '';
        $pdfLink = $pdfHref ? "https://www.verkuendung-bayern.de{$pdfHref}" : '';
        $pdfLink = explode('#', $pdfLink)[0] ?? null;

        if ($pdfLink) {
            $meta = $pageCrawl->pageUrl->frontierMeta->meta ?? [];
            $meta['source_url'] = $url;
            $meta['download_url'] = $pdfLink;
            $meta['work_date'] = Carbon::parse($meta['work_date']);
            $meta['source_unique_id'] = Str::of($pdfLink)->afterLast('/')->before('.pdf');
            $docMetaDto = new DocMetaDto($meta);

            $l = new UrlFrontierLink(['url' => $pdfLink]);
            $l->_metaDto = $docMetaDto;

            $this->arachno->followLink($pageCrawl, $l, true);
        }
    }

    protected function getHtmlContent(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '#gvbl_container',
            ],
        ]);

        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head/link[@rel="stylesheet"]',
            ],
        ]);

        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries);
    }
}
