<?php

namespace App\Services\Arachno\Crawlers\Sources\Mx;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @codeCoverageIgnore
 * slug: mx-ojp-puebla-gob
 * title: Government of the state of pueba
 * url: https://ojp.puebla.gob.mx/
 */
class MexicoStateOfPueba extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'mx-ojp-puebla-gob',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://ojp.puebla.gob.mx/legislaciondelestado?catid=19',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://ojp.puebla.gob.mx/legislaciondelestado?catid=242',
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
            && $this->arachno->matchUrl($pageCrawl, '/ojp\.puebla\.gob\.mx\/legislaciondelestado\?/')) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $this->handleMeta($pageCrawl);
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/ojp\.puebla\.gob\.mx\/legislaciondelestado\?/')
            || $this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => ['render_js' => false],
            ]);
        }
    }

    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.files-download', function (DOMElement $element) use ($pageCrawl) {
            $crawler = new Crawler($element, $pageCrawl->pageUrl->url, 'https://ojp.puebla.gob.mx');

            /** @var DOMElement $title */
            $title = $crawler->filter('h3.fs-15.fw-500')->getNode(0);
            $title = $title->textContent;

            /** @var DOMElement $dateText */
            $dateText = $crawler->filter('.fs-11.attachments')->getNode(0);
            $pubDate = Str::of($dateText->textContent)->after(':')->before('.');
            $pubDate = Carbon::parseFromLocale(str_replace('de', '', trim($pubDate)), 'es');

            $meta['work_date'] = $pubDate;

            $url = $crawler->filter('.itemLinks .button-download')->link()->getUri();
            $uniqueId = Str::of($url)->after('attachments/')->before('.pdf');

            $catDoc = new CatalogueDoc([
                'title' => $title,
                'source_unique_id' => $uniqueId,
                'start_url' => $url,
                'view_url' => $url,
                'primary_location_id' => 96241,
                'language_code' => 'spa',
            ]);

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);
        });

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $this->followNextLink($pageCrawl);
        }
    }

    protected function followNextLink(PageCrawl $pageCrawl): void
    {
        $next = $pageCrawl->domCrawler->filter('a[title="Siguiente"]')->getNode(0);

        if ($next) {
            $nextPage = $pageCrawl->domCrawler->filter('a[title="Siguiente"]')->link()->getUri();
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $nextPage]));
        }
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
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
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 96241);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'law');

        try {
            $date = $catalogueDoc->docMeta->doc_meta['work_date'] ?? null;

            $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $date);
            $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $date);
        } catch (InvalidFormatException) {
        }
        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }
}
