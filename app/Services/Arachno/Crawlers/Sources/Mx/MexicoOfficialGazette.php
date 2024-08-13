<?php

namespace App\Services\Arachno\Crawlers\Sources\Mx;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 * slug: mx-dof-gob
 * title: Diario Oficial De La Federacion
 * url: https://www.dof.gob.mx/
 */
class MexicoOfficialGazette extends AbstractCrawlerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/dof\.gob\.mx(?:\/|(?:\/#gsc\.tab=0)|(?:\/index_\d+\.php.*year.*))?$/')) {
                $this->handleForUpdatesIndex($pageCrawl);
            }
        }

        if ($this->arachno->matchUrl($pageCrawl, '/gob\.mx\/nota_detalle\.php\?codigo=\d+/')) {
            $this->capturePageContent($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
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
    protected function handleForUpdatesIndex(PageCrawl $pageCrawl): void
    {
        $dom = $pageCrawl->domCrawler;
        $pdfGazetteLinks = $dom->filterXPath('(//td[@class="Titulo"]//a[@target="_blank"][img[@title="Ver Imagen"]])[1]');
        /** @var DOMElement $pdfGazetteLink */
        $pdfGazetteLink = $pdfGazetteLinks->getNode(0);
        $date = $pdfGazetteLinks->count() !== 0 ? Str::of($pdfGazetteLink->getAttribute('href'))->after('&fecha=')->before('&')->before('#') : null;

        $this->arachno->eachX($pageCrawl, '//td[@id="tdcontent"]//td[3][not(contains(@class,"Titulo"))]//a[contains(@class, "enlaces")]',
            function (DOMElement $doc) use ($pageCrawl, $date) {
                $baseLink = 'https://www.dof.gob.mx';
                $title = $doc->textContent ?? '';
                $title = preg_replace('/[\t\n]+/', '', $title);

                $uniqueId = Str::of($doc->getAttribute('href'))->after('codigo=')->before('&');
                $htmlUrl = "{$baseLink}{$doc->getAttribute('href')}";

                $docMeta = new DocMetaDto();
                $docMeta->title = $title;
                $docMeta->source_url = $htmlUrl;
                $docMeta->download_url = $htmlUrl;
                $docMeta->language_code = 'spa';
                $docMeta->primary_location = '95766';
                $docMeta->source_unique_id = "updates_codigo/{$uniqueId}";
                $docMeta->work_number = $uniqueId;
                $docMeta->work_type = 'gazette';

                if ($date) {
                    try {
                        $workDate = Carbon::createFromFormat('d/m/Y', $date);
                        if ($workDate) {
                            $docMeta->work_date = Carbon::parse($workDate->format('Y-m-d'));
                        }
                    } catch (InvalidFormatException $th) {
                    }
                }

                $link = new UrlFrontierLink(['url' => $htmlUrl]);
                $link->_metaDto = $docMeta;
                $this->arachno->followLink($pageCrawl, $link, true);
            });

        $morningEdition = $dom->filterXPath('(//td[@class="Titulo"])[2]//a//span[text()[contains(.,"Edición Matutina")]]');
        if ($morningEdition->count() !== 0) {
            $this->arachno->followLinksXpath($pageCrawl, '(//td[@class="Titulo"])[2]//a'); // if day has 2 editions, evening edition displayed by default, add morning to queue
        }
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
                'query' => 'div#DivDetalleNota',
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

        $preStore = function (DomCrawler $crawler) {
            /** @var DOMElement $content */
            $content = $crawler->filter('#DivDetalleNota')->getNode(0);
            $content->setAttribute('style', str_replace('695px', '100%', $content->getAttribute('style')));

            return $crawler;
        };

        $this->arachno->capture(
            $pageCrawl,
            $bodyQueries,
            $headQueries,
            preStoreCallable: $preStore
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'mx-dof-gob',
            'throttle_requests' => 300,
            'start_urls' => [
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.dof.gob.mx/',
                    ]),
                ],
            ],
        ];
    }
}
