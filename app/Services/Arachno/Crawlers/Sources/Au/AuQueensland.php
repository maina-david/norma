<?php

namespace App\Services\Arachno\Crawlers\Sources\Au;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Carbon;
use Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 * slug: au-queensland
 * title: legislation.qld.gov.au
 * url: https://www.legislation.qld.gov.au/
 */
class AuQueensland extends AbstractCrawlerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/epub\?bulletin=/')) {
                $this->handleForUpdates($pageCrawl);
            }

            if ($this->arachno->matchUrl($pageCrawl, '/\/view\/html/')) {
                $this->getDownloadLink($pageCrawl);
            }

            if ($this->arachno->matchUrl($pageCrawl, '/\/feed\?id=title/')) {
                $this->getDate($pageCrawl);
            }
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\/view\/pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'au-queensland',
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    // new UrlFrontierLink([
                    //     'url' => 'https://www.legislation.qld.gov.au/feed?id=newinforce',
                    // ]),
                    // new UrlFrontierLink([
                    //     'url' => 'https://www.legislation.qld.gov.au/feed?id=newlegislation',
                    // ]),
                    new UrlFrontierLink([
                        'url' => 'https://www.legislation.qld.gov.au/epub?bulletin=' . now()->subDays(7)->format('Ymd'),
                    ]),
                ],
            ],
        ];
    }

    public function handleForUpdates(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'div [href*="/view/htm"]', function (DOMElement $entry) use ($pageCrawl) {
            $domCrawler = new DomCrawler($entry);

            /** @var DOMElement|null $link */
            $link = $domCrawler->getNode(0);
            $href = $link?->getAttribute('href');
            $href = "https://www.legislation.qld.gov.au{$href}";

            $title = $domCrawler->getNode(0)?->textContent ?? '';
            $id = Str::of($href)->afterLast('/');

            $pdfLink = preg_replace('/\/view\/html/', '/view/pdf', $href);

            if ($pdfLink == '') {
                return;
            }
            $docMeta = new DocMetaDto();
            $docMeta->title = $title;
            $docMeta->language_code = 'eng';
            $docMeta->source_url = $href;
            $docMeta->primary_location = '394';
            $docMeta->download_url = $pdfLink;
            $docMeta->work_type = 'notice';

            $docMeta->source_unique_id = 'updates_' . $id;

            preg_match('/[0-9-]+[a-z]*/', $id, $matches);

            if (!empty($matches)) {
                $docMeta->work_number = substr($matches[0], 1);
            }

            $l = new UrlFrontierLink(['url' => $href]);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function getDownloadLink(PageCrawl $pageCrawl): void
    {
        $domCrawler = $pageCrawl->domCrawler;

        /** @var DOMElement|null $downloadLink */
        $downloadLink = $domCrawler->filter('.btn-group a[aria-label*="View PDF"]')->getNode(0) ?? null;

        if (!empty($downloadLink)) {
            $downloadLink = $downloadLink->getAttribute('href');
        }

        $downloadLink = "https://www.legislation.qld.gov.au{$downloadLink}";

        $keywords = [];

        /** @var DOMElement|null $pageKeyword */
        $pageKeyword = $domCrawler->filterXPath('//head/meta[@name="keywords"]')->getNode(0);
        $keyword = $pageKeyword?->getAttribute('content');

        if (!empty($keyword)) {
            /** @var string $splitKeywords */
            $splitKeywords = preg_replace("/\n+\s*/", ' ', $keyword);
            $keywords = explode(', ', $splitKeywords);
        }

        if (!empty($keywords)) {
            $this->arachno->setDocMetaProperty($pageCrawl, 'keywords', $keywords);
        }

        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $downloadLink);

        /** @var DOMElement|null $rssLink */
        $rssLink = $domCrawler->filter('.btn-group a[href*="feed"]')->getNode(0) ?? null;

        if (!empty($rssLink)) {
            $rssLink = 'https://www.legislation.qld.gov.au' . $rssLink->getAttribute('href');
        }

        $l = new UrlFrontierLink(['url' => $rssLink]);
        $this->arachno->followLink($pageCrawl, $l, true);
    }

    public function getDate(PageCrawl $pageCrawl): void
    {
        $this->arachno->eachX($pageCrawl, '//*[local-name()="entry"]', function (DOMElement $entry) use ($pageCrawl) {
            $domCrawler = new DomCrawler($entry);
            $updated = $domCrawler->filterXPath('//*[local-name()="updated"]')->getNode(0)?->textContent;

            try {
                $workDate = Carbon::parse($updated);
                $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $workDate);
            } catch (InvalidFormatException) {
            }
            $meta = $pageCrawl->getFrontierMeta();
            $l = new UrlFrontierLink(['url' => $meta['download_url']]);
            $this->arachno->followLink($pageCrawl, $l, true);
        });
    }
}
