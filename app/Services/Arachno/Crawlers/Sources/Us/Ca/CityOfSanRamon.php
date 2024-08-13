<?php

namespace App\Services\Arachno\Crawlers\Sources\Us\Ca;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @codeCoverageIgnore
 * slug: us-online-encodeplus
 * title: City of San Ramon
 * url: https://online.encodeplus.com/
 */
class CityOfSanRamon extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-online-encodeplus',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink(['url' => 'https://online.encodeplus.com/regs/sanramon-ca/doc-view.aspx?ajax=0&secid=3748&_=1713864148688']),
                ],
            ],
        ];
    }

    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/online\.encodeplus\.com\/regs\/sanramon-ca/')) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => ['render_js' => false],
            ]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/online\.encodeplus\.com\/regs\/sanramon-ca\/doc-view\.aspx\?ajax=0&secid=3748/')) {
            $response = Http::get('https://online.encodeplus.com/regs/sanramon-ca/doc-view.aspx?ajax=0&secid=3748&_=1713864148688');
            $this->arachno->setCrawlCookies($pageCrawl, $response->cookies);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/online\.encodeplus\.com\/regs\/sanramon-ca\/doc-view\.aspx\?ajax=0&secid=3748/')) {
            $this->handleUpdates($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\?id=/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    protected function handleUpdates(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'table tbody tr:nth-last-child(-n+15)',
            function (DOMElement $element) use ($pageCrawl) {
                $crawler = new Crawler($element);
                $docMetaDto = new DocMetaDto();

                if (!$crawler->filter('td a[href*="doclibrary"]')->getNode(0)) {
                    return;
                }

                //                $tableRow = $crawler->filterXPath('.//td/a[contains(@href, "doclibrary")]')->getNode(0);
                /** @var DOMElement $title */
                $title = $crawler->filter('td:nth-child(3)')->getNode(0);
                $title = $title->textContent;

                /** @var DOMElement $link */
                $link = $crawler->filterXPath('.//td/a[contains(@href, "doclibrary")]')->getNode(0);
                $link = "https://online.encodeplus.com/regs/sanramon-ca/{$link->getAttribute('href')}";

                $uniqueId = Str::of($link)->after('id=');

                /** @var DOMElement $date */
                $date = $crawler->filter('td:nth-child(2) span')->getNode(0);
                $date = $date->textContent;

                try {
                    $date = Carbon::parse($date);
                    $docMetaDto->work_date = $date;
                    $docMetaDto->effective_date = $date;
                } catch (InvalidFormatException) {
                }

                $docMetaDto->title = $title;
                $docMetaDto->source_unique_id = "updates_{$uniqueId}";
                $docMetaDto->primary_location = '186506';
                $docMetaDto->source_url = $link;
                $docMetaDto->download_url = $link;
                $docMetaDto->language_code = 'eng';
                $docMetaDto->work_type = 'code';
                $docMetaDto->work_number = $uniqueId;

                $l = new UrlFrontierLink(['url' => $link]);
                $l->_metaDto = $docMetaDto;
                $this->arachno->followLink($pageCrawl, $l, true);
            });
    }
}
