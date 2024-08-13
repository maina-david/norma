<?php

namespace App\Services\Arachno\Crawlers\Sources\Mx;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use DOMElement;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @codeCoverageIgnore
 * slug: mx-legislacion-edomex-gob
 * title: State Government of Mexico
 * url: https://legislacion.edomex.gob.mx/
 */
class MexicoStateGovernment extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'mx-legislacion-edomex-gob',
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink(['url' => 'https://legislacion.edomex.gob.mx/leyes/vigentes']),
                    new UrlFrontierLink(['url' => 'https://legislacion.edomex.gob.mx/codigos/vigentes']),
                    new UrlFrontierLink(['url' => 'https://legislacion.edomex.gob.mx/index.php/reglamento-de-ley']),
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
            && $this->arachno->matchUrl($pageCrawl, '/legislacion\.edomex\.gob\.mx\/(leyes|codigos|index\.php)\//')) {
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
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl) || $this->arachno->crawlIsFetchWorks($pageCrawl)) {
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
        $this->arachno->each($pageCrawl, '.accordion-item', function (DOMElement $element) use ($pageCrawl) {
            $crawler = new Crawler($element, 'https://legislacion.edomex.gob.mx', $pageCrawl->pageUrl->url);

            /** @var DOMElement $title */
            $title = $crawler->filter('.accordion-header')->getNode(0);
            $title = $title->textContent;

            $url = $crawler->filter('.accordion-collapse.collapse a')->link()->getUri();
            $uniqueId = str_contains($url, '/pdf/')
                ? Str::of($url)->after('/pdf/')->before('.pdf')
                : Str::of($url)->afterLast('.mx/')->before('.pdf');

            /** @var DOMElement|null $summary */
            $summary = $crawler->filter('.accordion-collapse.collapse > div > p:nth-child(2)')->getNode(0) ?? '';
            $summary = $summary ? $summary->textContent : '';

            $catalogueDoc = new CatalogueDoc([
                'title' => $title,
                'source_unique_id' => $uniqueId,
                'start_url' => $url,
                'view_url' => $url,
                'primary_location_id' => 97116,
                'language_code' => 'spa',
                'summary' => $summary,
            ]);

            $this->arachno->createCatalogueDoc($pageCrawl, $catalogueDoc);
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

        $types = ['ley', 'código'];
        $urlText = explode(' ', strtolower($catalogueDoc->title ?? ''));
        $workType = array_values(array_intersect($urlText, $types))[0] ?? '';
        $workType = $this->getWorkType($workType);

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'spa');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 97116);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $workType);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'summary', $catalogueDoc->summary);

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }

    /**
     * @param string $workType
     *
     * @return string
     */
    protected function getWorkType(string $workType): string
    {
        return match (strtolower($workType)) {
            'código' => 'code',
            'ley' => 'law',
            default => 'regulation'
        };
    }
}
