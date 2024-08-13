<?php

namespace App\Services\Arachno\Crawlers\Sources\Nl;

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
 * slug: nl-officielebekendmakingen
 * title: Netherlnds Officielebekendmakingen
 * url: https://www.officielebekendmakingen.nl/
 */
class NlOfficielebekendmakingen extends AbstractCrawlerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/officielebekendmakingen\.nl\/resultaten\?.*/')) {
                $this->handleMeta($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/\.pdf$/')) {
                $this->arachno->capturePDF($pageCrawl);
            }
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleMeta(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'div#Publicaties > ul > li', function (DOMElement $item) use ($pageCrawl) {
            $entryCrawler = new DomCrawler($item);
            /** @var DOMElement */
            $pdfLink = $entryCrawler->filter('a[href*=".pdf"]')->getNode(0);
            $pdfHref = $pdfLink->getAttribute('href');
            $downloadUrl = 'https://www.officielebekendmakingen.nl/' . $pdfHref;
            $title = $entryCrawler->filter('p')->getNode(0)?->textContent ?? '';
            $workNumber = $entryCrawler->filter('dl div:nth-child(2) dd')->getNode(0)?->textContent ?? '';
            $date = $entryCrawler->filter('dl div:first-child dd')->getNode(0)?->textContent ?? '';
            $docMeta = new DocMetaDto();
            $docMeta->source_unique_id = Str::of($downloadUrl)->afterLast('/')->replace('.pdf', '');
            $docMeta->language_code = 'nld';
            $docMeta->title = $title;
            $docMeta->work_type = 'gazette';
            $docMeta->work_number = $workNumber ? explode(',', trim($workNumber))[1] : '';
            $docMeta->primary_location = '42319';
            $docMeta->source_url = Str::of($downloadUrl)->replace('.pdf', '.html');
            $docMeta->download_url = $downloadUrl;
            if ($date) {
                try {
                    $docMeta->work_date = Carbon::parse($date);
                } catch (InvalidFormatException $th) {
                }
            }
            $l = new UrlFrontierLink(['url' => $downloadUrl, 'anchor_text' => 'PDF']);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'nl-officielebekendmakingen',
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.officielebekendmakingen.nl/resultaten?zv=&pg=150&col=&svel=Titel&svol=Oplopend&pubs=Staatsblad',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://www.officielebekendmakingen.nl/resultaten?zv=&pg=150&col=&svel=Titel&svol=Oplopend&pubs=Staatscourant&oty=ministerie',
                    ]),
                ],
            ],
        ];
    }
}
