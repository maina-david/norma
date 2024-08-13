<?php

namespace App\Services\Arachno\Crawlers\Sources\Nz;

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
 * slug: nz-new-zealand-federal-gazette
 * title: New Zealand Federal Gazette
 * url: https://gazette.govt.nz/home/NoticeSearch?noticeType=sl
 */
class NewZealandFederalGazette extends AbstractCrawlerConfig
{
    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/\/home\/NoticeSearch\?noticeType=sl$/')) {
            $this->arachno->followLinksXpath($pageCrawl, '//tr[.//td[.//*[contains(text(), "Legislation Act")]] or .//td//a//*[contains(text(), "MPI")]]//td/a[contains(@href,"/notice")]');
        }
        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/gazette\.govt\.nz\/notice\/id\/.*/')) {
            $this->handleMeta($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/gazette\.govt\.nz\/notice\/pdf\/.*/')) {
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
        /** @var string $downloadUrl */
        $downloadUrl = Str::replace('/id/', '/pdf/', $url);
        $docMeta = new DocMetaDto();
        $docMeta->source_unique_id = $dom->filter('div.meta> dl > dd')->getNode(0)?->textContent ?? null;
        $title = $dom->filter('div.title-panel > dl > dd > h2.notice-title')->getNode(0)?->textContent ?? null;
        $docMeta->title = $title;
        $docMeta->language_code = 'eng';
        $docMeta->primary_location = '98265';
        $docMeta->work_type = 'notice';
        $docMeta->summary = $dom->filter('div.content-panel div.text p:first-child')->getNode(0)?->textContent ?? null;
        $docMeta->work_number = $dom->filter('div.meta > dl > dd')->getNode(0)?->textContent ?? null;
        $docMeta->source_url = $url;
        $docMeta->download_url = $downloadUrl;
        $date = $dom->filter('div.meta-panel > dl > dd')->getNode(0)?->textContent ?? '';
        $date = preg_replace('/\s+/', ' ', $date);
        if ($date) {
            try {
                /** @var Carbon */
                $workDate = Carbon::createFromFormat('j M Y', trim($date));
                $docMeta->work_date = $workDate;
            } catch (InvalidFormatException $th) {
            }
        }
        $keywords = [];
        foreach ($dom->filter('div.tags.block dl dd a') as $cat) {
            /** @var DOMElement $cat */
            $keyword = $cat->textContent;
            $keywords[] = $keyword;
        }

        $docMeta->keywords = !empty($keywords) ? $keywords : null;
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
            'for_update' => true,
            'throttle_requests' => 200,
            'start_urls' => $this->getCustomCodesStartUrls(),
        ];
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string,array<UrlFrontierLink>>
     */
    protected function getCustomCodesStartUrls(): array
    {
        return ['type_' . CrawlType::FOR_UPDATES->value => [
            new UrlFrontierLink(['url' => 'https://gazette.govt.nz/home/NoticeSearch?noticeType=sl']),
        ]];
    }
}
