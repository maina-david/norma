<?php

namespace App\Services\Arachno\Crawlers\Sources\Br;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use DOMElement;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * NB: not complete! just handling ordinary laws for now... "LEIS ORDINÁRIAS".
 *
 * Documents should only be fetched via the catalogue, as that's where the titles and source_unique_id are set. We don't attempt
 * to parse the capture page for titles and source_unique_id
 *
 * @codeCoverageIgnore
 * slug: br-brazil-federal-planalto
 * title: Brazil Federal Planalto
 * url: http://www4.planalto.gov.br/
 */
class BrazilFederalPlanalto extends AbstractCrawlerConfig
{
    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'br-brazil-federal-planalto',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'http://www4.planalto.gov.br/legislacao/portal-legis/legislacao-1/leis-ordinarias',
                    ]),
                ],
            ],
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        // the page with all the dates for ordinary laws
        if ($this->arachno->matchUrl($pageCrawl, '/\/legislacao\/portal\-legis\/legislacao\-1\/leis\-ordinarias$/')) {
            $this->arachno->followLinksCSS($pageCrawl, '#content-core #parent-fieldname-text a');
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\/legislacao\/portal\-legis\/legislacao\-1\/leis\-ordinarias\//')) {
            $this->handleOrdinaryLawsCatalogue($pageCrawl);
        }
        if ($this->arachno->matchUrl($pageCrawl, '/planalto\.gov\.br\/ccivil\_03\//')) {
            $this->handleMeta($pageCrawl);
            $this->capturePageContent($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleOrdinaryLawsCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->eachX($pageCrawl, '//*[@id="visao2"]/table/tbody/tr/td[1]/a', function (DOMElement $link) use ($pageCrawl) {
            $trCrawler = new DomCrawler($link->parentNode?->parentNode ?? '');
            $href = $link->getAttribute('href');
            $id = (string) Str::of($href)
                ->after('.gov.br')
                ->replace('.htm', '');
            $title = trim($link->textContent ?? '');
            $summary = $trCrawler->filterXPath('//td[2]')->getNode(0)?->textContent ?? null;
            $summary = $summary ? trim($summary) : null;
            $catDoc = new CatalogueDoc([
                'title' => $title,
                'start_url' => $href,
                'view_url' => $href,
                'source_unique_id' => $id,
                'language_code' => 'por',
                'summary' => $summary,
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
        $url = $pageCrawl->getFinalRedirectedUrl();
        /** @var CatalogueDoc */
        $catDoc = $pageCrawl->pageUrl->catalogueDoc;
        $id = $catDoc->source_unique_id;

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'por');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '77838');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $url);

        return $doc;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function capturePageContent(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'body',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//title',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//style',
            ],
        ]);

        $preStore = function ($dom) {
            foreach ($dom->filter('font') as $element) {
                $element->removeAttribute('color');
            }

            return $dom;
        };

        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, [], null, $preStore);
    }
}
