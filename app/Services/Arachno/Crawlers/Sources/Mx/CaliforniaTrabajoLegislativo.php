<?php

namespace App\Services\Arachno\Crawlers\Sources\Mx;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Carbon;
use Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 * slug: mx-california-trabajo-legs
 * title: Baja, California Trabajo Legislativo
 * url: https://www.congresobc.gob.mx/
 */
class CaliforniaTrabajoLegislativo extends AbstractCrawlerConfig
{
    protected string $baseDomain = 'https://www.congresobc.gob.mx';

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'mx-california-trabajo-legs',
            'throttle_requests' => 500,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.congresobc.gob.mx/TrabajoLegislativo/Leyes',
                    ]),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/TrabajoLegislativo\/Leyes/')) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $this->handleMeta($pageCrawl);
            if ($this->arachno->matchUrl($pageCrawl, '/\.PDF/')) {
                $this->arachno->capturePDF($pageCrawl);
            }
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'tr > td > a[href*=".PDF"]', function ($link) use ($pageCrawl) {
            $trCrawler = new DomCrawler($link->parentNode->parentNode);
            /** @var DOMElement $link */
            $href = $trCrawler->filter('td:nth-child(2) a')->getNode(0);
            $href = $link->getAttribute('href');
            $href = Str::of($href)->replace('..', '');
            $fullLink = "{$this->baseDomain}{$href}";
            $id = (string) Str::of($href)
                ->after('Leyes/')
                ->replace('.PDF', '');
            $title = $trCrawler->filter('td:nth-child(1)')->getNode(0);
            $title = trim($title?->textContent ?? '');
            $date = $trCrawler->filter('td:nth-child(4)')->getNode(0);
            $date = $date?->textContent ?? '';
            $catDoc = new CatalogueDoc([
                'title' => $title,
                'start_url' => $fullLink,
                'view_url' => $fullLink,
                'source_unique_id' => $id,
                'language_code' => 'spa',
                'primary_location_id' => '98240',
            ]);

            $meta = [
                'work_date' => $date,
            ];

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleMeta(PageCrawl $pageCrawl): void
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        /** @var CatalogueDocMeta */
        $catDocMeta = CatalogueDocMeta::find($catalogueDoc->id);
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'spa');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '98240');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'law');
        $date = $catDocMeta->doc_meta['work_date'] ?? null;
        if ($date) {
            try {
                /** @var Carbon */
                $workDate = Carbon::createFromFormat('Y/m/d', trim($date));
                $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $workDate->format('Y-m-d'));
            } catch (InvalidFormatException $th) {
            }
        }
        $downloadUrl = $catalogueDoc->start_url;
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $downloadUrl);
        $l = new UrlFrontierLink(['url' => $downloadUrl, 'anchor_text' => 'PDF']);
        $this->arachno->followLink($pageCrawl, $l, true);

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }
}
