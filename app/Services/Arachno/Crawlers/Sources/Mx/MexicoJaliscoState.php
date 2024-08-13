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
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @codeCoverageIgnore
 *
 * slug: mx-congresoweb-congresojal
 * title: Jalisco State
 * url: https://www.congresojal.gob.mx/
 */
class MexicoJaliscoState extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'mx-congresoweb-congresojal',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => "https://congresoweb.congresojal.gob.mx/BibliotecaVirtual/busquedasleyes/Listado'2.cfm#Codigos",
                    ]),
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/congresoweb\.congresojal\.gob\.mx\/BibliotecaVirtual\/busquedasleyes\/Listado/')) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $this->handleMeta($pageCrawl);
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => false,
                ],
            ]);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => false,
                ],
            ]);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.table.table-striped tbody tr', function (DOMElement $row) use ($pageCrawl) {
            $crawler = new Crawler($row, 'https://congresoweb.congresojal.gob.mx', $pageCrawl->pageUrl->url);
            $meta = [];

            $href = $crawler->filter('td a[href$=".pdf"]')->getNode(0);
            $href = $href ? $crawler->filter('td a[href$=".pdf"]')->link()->getUri() : '';

            if (!$href) {
                return;
            }
            /** @var DOMElement $workNumber */
            $workNumber = $crawler->filter('td:last-child')->getNode(0);

            $date = $workNumber->previousElementSibling?->textContent;

            try {
                $date = explode('/', $date ?? '');
                $date = implode('-', array_reverse($date));
                $workDate = Carbon::parse($date);

                $meta[] = [
                    'work_date' => $workDate,
                    'effective_date' => $workDate,
                ];
            } catch (InvalidFormatException) {
            }

            $workNumber = $workNumber->textContent;

            $uniqueId = Str::of($href)->afterLast('/')->before('.pdf');
            $uniqueId = explode(' ', $uniqueId);
            $uniqueId = implode('-', $uniqueId);

            /** @var DOMElement $title */
            $title = $crawler->filter('td.leyreg')->getNode(0);
            $title = $title->textContent;

            $catDoc = new CatalogueDoc([
                'title' => $title,
                'source_unique_id' => strtolower($uniqueId),
                'start_url' => $href,
                'view_url' => $href,
                'primary_location_id' => 97902,
                'language_code' => 'spa',
            ]);

            $meta['work_number'] = $workNumber;
            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);
        });
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getWorkType(string $type): string
    {
        return match (strtolower($type)) {
            'códigos' => 'code',
            'constitución' => 'constitution',
            'reglamentos' => 'regulation',
            default => 'law'
        };
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
        $catalogueDoc->load('docMeta');

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'spa');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 97902);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->docMeta?->doc_meta['work_number'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $catalogueDoc->docMeta?->doc_meta[0]['effective_date'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $catalogueDoc->docMeta?->doc_meta[0]['work_date'] ?? null);

        $type = Str::of($catalogueDoc->start_url ?? '')->after('legislacion/')->before('/');
        $workType = $this->getWorkType($type);

        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $workType);

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }
}
