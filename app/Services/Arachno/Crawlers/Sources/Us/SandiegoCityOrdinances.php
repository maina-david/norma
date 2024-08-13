<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as domCrawler;

/**
 * @codeCoverageIgnore
 *
 * slug: us-sandiego-city-ordinances
 * title: USA Sandiego City Resolutions and Ordinances
 * url: https://www.sandiego.gov/city-clerk/officialdocs
 */
class SandiegoCityOrdinances extends AbstractCrawlerConfig
{
    protected string $baseDomain = 'https://www.sandiego.gov';

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/city\-clerk\/officialdocs\/council\-resolutions\-ordinances$/')) {
            $this->hanldeUpdates($pageCrawl);
        }
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/city-clerk\/officialdocs\/municipal-code$/')) {
                $this->followLinks($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/city-clerk\/officialdocs\/municipal-code\/chapter/')) {
                $this->handleCatalogue($pageCrawl);
            }
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $this->handleMeta($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    public function preFetch(PageCrawl $pageCrawl): void
    {
        $pageCrawl->setProxySettings([
            'provider' => 'scraping_bee',
            'options' => ['render_js' => false],
        ]);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function followLinks(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'p a[href*="municipal-code/chapter"]', function (DOMElement $link) use ($pageCrawl) {
            /** @var DOMElement $link */
            $href = $link->getAttribute('href');
            $pdfLink = new UrlFrontierLink(['url' => "{$this->baseDomain}{$href}"]);
            $this->arachno->followLink($pageCrawl, $pdfLink);
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'div.field-content a[href*=".pdf"]', function (DOMElement $item) use ($pageCrawl) {
            $entryCrawler = new domCrawler($item->parentNode);
            $href = $entryCrawler->filter('a[href*=".pdf"]')->getNode(0);
            /** @var DOMElement $href */
            $pdfLink = $href->getAttribute('href');
            $uniqueId = (string) Str::of($pdfLink)->after('municode/')->replace('.pdf', '');
            $title = $href->textContent;
            $catDoc = new CatalogueDoc([
                'title' => trim($title),
                'start_url' => $pdfLink,
                'view_url' => $pdfLink,
                'source_unique_id' => $uniqueId,
                'language_code' => 'eng',
                'primary_location_id' => 69175,
            ]);

            $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc
     */
    protected function handleMeta(PageCrawl $pageCrawl): Doc
    {
        /** @var CatalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'eng');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 69175);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', (string) 'code');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->start_url);

        return $doc;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function hanldeUpdates(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'div.view-content tbody tr', function ($items) use ($pageCrawl) {
            $dom = new domCrawler($items, $pageCrawl->pageUrl->url, 'https://www.sandiego.gov');
            /** @var DOMElement $href */
            $href = $dom->filter('td:nth-child(3) a[href]')->getNode(0);
            $href = $href->getAttribute('href');
            $uniqueId = Str::of($href)->afterLast('ordinance/')->replace('.pdf', '');
            $docMeta = new DocMetaDto();
            $docMeta->source_unique_id = 'updates_' . $uniqueId;
            $title = $dom->filter('td:nth-child(3)')->getNode(0)?->textContent ?? null;
            $workNumber = $dom->filter('td:nth-child(2)')->getNode(0)?->textContent ?? null;
            $workDate = $dom->filter('td:nth-child(1)')->getNode(0)?->textContent ?? null;
            $docMeta->title = $title;
            $docMeta->work_number = $workNumber;
            $docMeta->language_code = 'eng';
            $docMeta->primary_location = '69175';
            $docMeta->work_type = 'ordinance';
            $docMeta->source_url = $href;
            $docMeta->download_url = $href;

            if ($workDate) {
                try {
                    /** @var Carbon */
                    $workDate = Carbon::parse($workDate);
                    $docMeta->work_date = $workDate;
                    $docMeta->effective_date = $workDate;
                } catch (InvalidFormatException $th) {
                }
            }
            $l = new UrlFrontierLink(['url' => $href, 'anchor_text' => 'PDF']);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
        });
    }

    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'us-sandiego-city-ordinances',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink(['url' => 'https://www.sandiego.gov/city-clerk/officialdocs/council-resolutions-ordinances']),
                ],
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink(['url' => 'https://www.sandiego.gov/city-clerk/officialdocs/municipal-code']),
                ],
            ],
        ];
    }
}
