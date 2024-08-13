<?php

namespace App\Services\Arachno\Crawlers\Sources\De;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore
 * slug: de-germany-dguv-regs
 * title: Germany Dguv Regs
 * url: https://publikationen.dguv.de/regelwerk/dguv-vorschriften/?p=1&o=9
 */
class GermanyDguvRegs extends AbstractCrawlerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if (($this->arachno->crawlIsFullCatalogue($pageCrawl) || $this->arachno->crawlIsNewCatalogueWorkDiscovery($pageCrawl)) && $this->arachno->matchUrl($pageCrawl, '/publikationen\.dguv\.de\/regelwerk\/dguv[a-z-]+\/\?p=.*/')) {
            $this->handleCatalogue($pageCrawl);
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/publikationen\.dguv\.de\/regelwerk\/dguv[a-z-]+\/\d{4}.*/')) {
            $this->handleMeta($pageCrawl);
            $this->arachno->followLinksCSS($pageCrawl, 'div.dguv-button-wrapper a[href*="/widgets/pdf/download/article"]');
        }
        if ($this->arachno->matchUrl($pageCrawl, '/widgets\/pdf\/download\/article\/\d{4,}/')) {
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'div.switchable-item--top-container div.switchable-item--text-container a[href*="publikationen.dguv"]', function (DOMElement $link) use ($pageCrawl) {
            /** @var DOMElement $link */
            $href = $link->getAttribute('href');
            $uniqueId = Str::of($href)->match('/\d{4,}/');

            $catDoc = new CatalogueDoc([
                'title' => trim($link->getAttribute('title')),
                'start_url' => $href,
                'view_url' => $href,
                'source_unique_id' => '/widgets/pdf/download/article/' . $uniqueId, // Str::of($uniqueId)->match('/[A-Za-z0-9-]+/'),
                'language_code' => 'deu',
                'primary_location_id' => '42320',
            ]);
            $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
        });

        // Go to next page
        $this->arachno->followLinksCSS($pageCrawl, 'a[title="Nächste Seite"].paging--link.paging--next');
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc
     */
    protected function handleMeta(PageCrawl $pageCrawl): Doc
    {
        $dom = $pageCrawl->domCrawler;
        $url = (string) $pageCrawl->getFinalRedirectedUrl();
        /** @var DOMElement */
        $downloadLink = $dom->filter('div.dguv-button-wrapper a[href*="/widgets/pdf/download/article"]')->getNode(0) ?? null;
        $downloadLink = $downloadLink->getAttribute('href');
        $downloadUrl = "https://publikationen.dguv.de{$downloadLink}";
        /** @var CatalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'deu');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '42320');
        $workType = Str::of($url)->after('dguv-')->before('/')->before('/');
        $workType = match (trim($workType)) {
            'vorschriften' => 'regulation',
            'regeln' => 'rule',
            'informationen' => 'guidance',
            default => 'act',
        };
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $workType);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $downloadUrl);

        $date = $dom->filter('ul li:first-child > span.entry--content')->getNode(0)?->textContent ?? '';
        if ($date) {
            try {
                /** @var Carbon */
                $workDate = Carbon::createFromFormat('Y.m', trim($date));
                $formattedDate = Carbon::parse($workDate->format('Y-m-d'));
                $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $formattedDate);
            } catch (InvalidFormatException $th) {
            }
        }

        return $doc;
    }

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'de-germany-dguv-regs',
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://publikationen.dguv.de/regelwerk/dguv-vorschriften/?p=1&o=9',
                    ]),
                ],
            ],
        ];
    }
}
