<?php

namespace App\Services\Arachno\Crawlers\Sources\Cr;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Support\DomClosest;
use DOMElement;
use DOMNode;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @codeCoverageIgnore
 * slug: cr-cso-go
 * title: Costa Rican ministry of labour and social security
 * url: https://cso.go.cr
 */
class CostaRicaNationalCSO extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'cr-cso-go',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://cso.go.cr/legislacion/constitucion.aspx',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://cso.go.cr/legislacion/convenios_OIT.aspx',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://cso.go.cr/legislacion/leyes.aspx',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://cso.go.cr/legislacion/decretos.aspx',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://cso.go.cr/legislacion/reglamentos.aspx',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://cso.go.cr/legislacion/directrices.aspx',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://cso.go.cr/legislacion/criteriosTecnicosyJuridicos.aspx',
                    ]),
                    new UrlFrontierLink([
                        'url' => 'https://cso.go.cr/legislacion/normas_tecnicas.aspx',
                    ]),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            if ($this->arachno->matchCSS($pageCrawl, '.MsoTableGrid')) {
                $this->handleCatalogue($pageCrawl);
            }
            if ($this->arachno->matchCSS($pageCrawl, '.Link2Button p a')
                && $this->arachno->matchUrl($pageCrawl, '/go\.cr\/legislacion\/constitucion/')) {
                $this->getDocLink($pageCrawl);
            }
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/.pdf/')
            || $this->arachno->matchUrl($pageCrawl, '/.Pdf/')) {
            $this->handleMeta($pageCrawl);
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

    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '.MsoTableGrid tr.REPEATERROW',
            function (DOMElement $element) use ($pageCrawl) {
                $crawler = new Crawler($element, $pageCrawl->pageUrl->url, 'https://cso.go.cr');

                /** @var DOMElement $anchor */
                $anchor = $crawler->filter('td p a')->getNode(0);
                $title = $anchor->textContent;
                $title = preg_replace('/(?:Â)+|\+|\.pdf/i', '', $title);
                $link = $crawler->filter('td p a')->link()->getUri();

                $closest = app(DomClosest::class);
                /** @var DOMNode $anchorElem */
                $anchorElem = $crawler->filter('td p a')->getNode(0);
                /** @var DOMElement $anchorTd */
                $anchorTd = $closest->closest($anchorElem, 'td')?->getNode(0);
                $summaryTd = $anchorTd->nextElementSibling;
                $summary = $summaryTd?->textContent ?? '';
                /** @var string $summary */
                $summary = preg_replace('/\n+|\s+/', '', $summary);

                $uniqueId = $anchor->getAttribute('href');
                $uniqueId = Str::of($uniqueId)->after('/')->before('.');
                $uniqueId = trim($uniqueId);

                $type = $pageCrawl->pageUrl->url;
                $type = Str::of($type)->afterLast('/')->before('.');
                $workType = $this->getWorkType($type);

                $meta['work_type'] = $workType;

                $catDoc = new CatalogueDoc([
                    'title' => $title,
                    'source_unique_id' => urldecode($uniqueId),
                    'start_url' => $link,
                    'view_url' => $link,
                    'summary' => trim($summary),
                    'language_code' => 'spa',
                    'primary_location_id' => 172883,
                ]);

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
        return match ($type) {
            'constitucion' => 'constitution',
            'leyes' => 'law',
            'decretos' => 'decree',
            default => 'regulation'
        };
    }

    protected function getDocLink(PageCrawl $pageCrawl): void
    {
        $anchors = $pageCrawl->domCrawler->filter('.Link2Button p a');

        /** @var DOMElement $anchor */
        foreach ($anchors as $anchor) {
            $title = $anchor->getAttribute('title');
            $title = Str::of($title)->before('.pdf');

            $href = $anchor->getAttribute('href');
            $href = str_replace('+', ' ', $href);
            $link = str_contains($href, 'https://cso.go.cr') ? $href : "https://cso.go.cr{$href}";

            $uniqueId = Str::of($href)->after('/')->before('.');
            $uniqueId = urldecode($uniqueId);

            $catDoc = new CatalogueDoc([
                'title' => $title,
                'source_unique_id' => trim($uniqueId),
                'start_url' => $link,
                'view_url' => $link,
                'language_code' => 'spa',
                'primary_location_id' => 172883,
            ]);

            $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
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
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 172883);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $catalogueDoc->docMeta?->doc_meta['work_type'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'summary', $catalogueDoc->summary);

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }
}
