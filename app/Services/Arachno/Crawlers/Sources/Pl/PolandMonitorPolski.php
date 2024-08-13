<?php

namespace App\Services\Arachno\Crawlers\Sources\Pl;

use App\Enums\Arachno\CrawlType;
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
 * slug: pl-monitorpolski-gov
 * title: Monitor Polski
 * url: https://monitorpolski.gov.pl/MP
 */
class PolandMonitorPolski extends AbstractCrawlerConfig
{
    protected string $baseLink = 'https://monitorpolski.gov.pl';

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'pl-monitorpolski-gov',
            'throttle' => 500,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink(['url' => 'https://monitorpolski.gov.pl/MP/rok/' . now()->year]),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/monitorpolski\.gov\.pl\/MP\/rok/')) {
            $this->handleUpdates($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
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
        $this->arachno->each($pageCrawl, '#c_table tr:not([class="noBorder"])', function (DOMElement $rows) use ($pageCrawl) {
            $domCrawler = new DomCrawler($rows);

            $docMetaDto = new DocMetaDto();

            /** @var DOMElement|null $title */
            $title = $domCrawler->filter('td.numberAlign')->getNode(0);
            $docMetaDto->title = trim($title->nextElementSibling->textContent ?? '');

            /** @var DOMElement $uniqueId */
            $uniqueId = $domCrawler->filter('td a:not([href*=".pdf"])')->getNode(0);
            $docMetaDto->source_unique_id = trim($uniqueId->getAttribute('href'), '/');

            /** @var DOMElement|null $downloadElem */
            $downloadElem = $domCrawler->filter('td a[href*=".pdf"]')->getNode(0);
            $downloadLink = $downloadElem?->getAttribute('href') ?? '';
            $docMetaDto->work_number = $downloadLink ? Str::of($downloadLink)->afterLast('/')->before('.pdf') : null;

            /** @var DOMElement|null $sourceLinkElem */
            $sourceLinkElem = $domCrawler->filter('td a:not([href*=".pdf"])')->getNode(0);
            $sourceLink = $sourceLinkElem?->getAttribute('href') ?? '';

            $docMetaDto->source_url = $this->baseLink . $sourceLink;
            $docMetaDto->language_code = 'pol';
            $docMetaDto->primary_location = '158667';
            $docMetaDto->work_type = 'act';
            $docMetaDto->download_url = $this->baseLink . $downloadLink;

            try {
                /** @var DOMElement $date */
                $date = $domCrawler->filter('td:last-child')->getNode(0);
                $date = trim($date->textContent);
                $docMetaDto->work_date = Carbon::parse($date);
            } catch (InvalidFormatException) {
            }

            $link = new UrlFrontierLink(['url' => $this->baseLink . $downloadLink]);
            $link->_metaDto = $docMetaDto;
            $this->arachno->followLink($pageCrawl, $link, true);
        });

        /** @var DOMElement|null $secondLastPage */
        $secondLastPage = $pageCrawl->domCrawler->filter('div.frigth a:nth-last-child(2)')->getNode(0);
        if ($secondLastPage) {
            $secondLastPage = $this->baseLink . $secondLastPage->getAttribute('href');
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $secondLastPage, 'anchor_text' => 'Next']));
        }
    }
}
