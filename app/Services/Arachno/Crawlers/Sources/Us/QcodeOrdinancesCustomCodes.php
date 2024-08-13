<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Services\Arachno\Support\DomClosest;
use DOMElement;
use DOMNode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

/**
 * @codeCoverageIgnore
 *
 * slug: us-qcode-ordinances-custom-codes
 * title: USA QCode Ordinances Custom Codes
 * url: https://content.qcode.us/publications.html
 */
class QcodeOrdinancesCustomCodes extends AbstractCrawlerConfig
{
    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/\.codes\/enactments$/')) {
            $this->handleEnactmentsPage($pageCrawl);
        }
        // e.g. https://bakersfield.municipal.codes/enactments/Ord5095/media/original.pdf
        if ($this->arachno->matchUrl($pageCrawl, '/\.pdf$/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    protected function handleEnactmentsPage(PageCrawl $pageCrawl): void
    {
        $dom = $pageCrawl->domCrawler;
        // e.g. https://bakersfield.municipal.codes/enactments
        $baseUrl = $pageCrawl->getFinalRedirectedUrl();
        $baseUrl = Str::beforeLast($baseUrl, '/');
        $prefix = str_replace('https://', '', $baseUrl);
        $prefix = Str::before($prefix, '.');
        $jurisdiction = $dom->filterXPath('//head//meta[contains(@property,"og:site_name")]/@content')->getNode(0)?->textContent ?? '';
        $locations = $this->getQcodeLocations();
        foreach ($dom->filter('.enactmentlist table tbody tr .enactmentlist-num a[href*=".pdf"]') as $link) {
            /** @var DOMElement $link */
            $href = $link->getAttribute('href');
            $href = $baseUrl . $href;
            $closest = app(DomClosest::class);
            /** @var DOMNode $link */
            /** @var Crawler $tr */
            $tr = $closest->closest($link, 'tr');
            $title = $tr->filter('td:nth-child(1) a:first-of-type')->getNode(0)?->textContent ?? '';
            $title = trim($title);
            $docMeta = new DocMetaDto();
            $titleNoSpaces = preg_replace(['/\s+/', '/\./'], '', $title);
            $docMeta->source_unique_id = implode('_', [$prefix, $titleNoSpaces]);
            $docMeta->title = implode(' - ', [$jurisdiction, $title]);

            $date = $tr->filter('td:nth-child(3)')->getNode(0)?->textContent ?? null;
            try {
                if ($date) {
                    /** @var Carbon */
                    $workDate = Carbon::createFromFormat('n/j/Y H:i', $date . ' 12:00');
                    $docMeta->work_date = $workDate;
                }
            } catch (Throwable $th) {
            }
            $summary = $tr->filter('td:nth-child(2)')->getNode(0)?->textContent ?? null;
            $docMeta->summary = $summary;
            $number = str_replace('Ord. ', '', $title);
            $docMeta->work_number = $number;
            $docMeta->download_url = $href;
            $docMeta->source_url = $href;
            $docMeta->work_type = 'ordinance';
            $docMeta->language_code = 'eng';
            // e.g. https://bakersfield.municipal.codes/enactments/Ord5095/media/original.pdf
            $docMeta->primary_location = (string) ($locations[$prefix] ?? '');

            $l = new UrlFrontierLink(['url' => $href, 'anchor_text' => $title]);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
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
        $urls = [
            'https://bakersfield.municipal.codes/enactments',
            // 'https://sanluisobispo.municipal.codes/enactments', // doesn't seem to publish the ordinances online, even though they have a table of ordinances (but no PDF links)
            // 'https://chulavista.municipal.codes/enactments',  // doesn't seem to publish the ordinances online, even though they have a table of ordinances (but no PDF links)
        ];
        $out = [];
        foreach ($urls as $u) {
            $out[] = new UrlFrontierLink(['url' => $u]);
        }

        return ['type_' . CrawlType::FOR_UPDATES->value => $out];
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string, int>
     */
    protected function getQcodeLocations(): array
    {
        // Chik-Fil-A jurisdictions commented out
        return [
            //            'bakersfield' => 68985,
            //            'sanluisobispo' => 69195,
            //            'chulavista' => 88735,
        ];
    }
}
