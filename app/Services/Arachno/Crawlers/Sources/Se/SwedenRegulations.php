<?php

namespace App\Services\Arachno\Crawlers\Sources\Se;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore
 *
 * slug: se-sweden-regulations
 * title: Kingdon of Sweden
 * url: https://svenskforfattningssamling.se/
 */
class SwedenRegulations extends AbstractCrawlerConfig
{
    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/svenskforfattningssamling\.se\//')) {
            $this->arachno->followLinksCSS($pageCrawl, 'a[href*="doc/"]');
        }
        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/.*svenskforfattningssamling\.se\/doc\/\d+\.html/')) {
            $this->handleMeta($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/sfs\/[0-9-]+\/[A-Z0-9-]+\.pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleMeta(PageCrawl $pageCrawl): void
    {
        $dom = $pageCrawl->domCrawler;
        $url = $pageCrawl->getFinalRedirectedUrl();
        /** @var DOMElement */
        $downloadUrl = $dom->filter('ul li.field--item a[href*="pdf"]')->getNode(0);
        $href = $downloadUrl->getAttribute('href');
        $href = str_replace('../sites', 'sites', $href);
        $downloadUrl = 'https://svenskforfattningssamling.se/' . $href;
        $docMeta = new DocMetaDto();
        $docMeta->source_unique_id = Str::of($downloadUrl)->after('sfs/')->before('.pdf');
        $title = $dom->filter('h1.page-header')->getNode(0)?->textContent ?? null;
        $docMeta->title = $title;
        $docMeta->language_code = 'swe';
        $docMeta->primary_location = '111914';
        $docMeta->work_type = 'regulation';
        $docMeta->work_number = $dom->filter('div#content div.field--name-sfs-number > div.field--item')->getNode(0)?->textContent ?? null;
        $docMeta->source_url = $url;
        $docMeta->download_url = $downloadUrl;
        $date = $dom->filter('div.field--type-datetime div.field--item')->getNode(0)?->textContent ?? '';
        if ($date) {
            try {
                $docMeta->work_date = Carbon::parse($date);
            } catch (InvalidFormatException $th) {
            }
        }
        $l = new UrlFrontierLink(['url' => $downloadUrl, 'anchor_text' => 'PDF']);
        $l->_metaDto = $docMeta;
        $this->arachno->followLink($pageCrawl, $l, true);
    }

    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'se-sweden-regulations',
            'throttle_requests' => 200,
            'start_urls' => $this->getSwedenStartUrls(),
        ];
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string,array<UrlFrontierLink>>
     */
    protected function getSwedenStartUrls(): array
    {
        return ['type_' . CrawlType::FOR_UPDATES->value => [
            new UrlFrontierLink(['url' => 'https://svenskforfattningssamling.se/regulations%3Fpage=0.html']),
            new UrlFrontierLink(['url' => 'https://svenskforfattningssamling.se/regulations%3Fpage=1.html']),
            new UrlFrontierLink(['url' => 'https://svenskforfattningssamling.se/regulations%3Fpage=2.html']),
            new UrlFrontierLink(['url' => 'https://svenskforfattningssamling.se/regulations%3Fpage=3.html']),
        ]];
    }
}
