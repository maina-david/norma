<?php

namespace App\Services\Arachno\Crawlers\Sources\Es;

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
use Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 *
 * slug: es-parlamentodeandalucia
 * title: City of Grenada, Parliament of Andalucia
 * url: https://www.parlamentodeandalucia.es
 */
class SpainAndaluciaParliament extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'es-parlamentodeandalucia',
            'throttle_requests' => 500,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.parlamentodeandalucia.es/webdinamica/portal-web-parlamento/recursosdeinformacion/coleccionlegislativa.do',
                    ]),
                ],
                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink([
                        'url' => 'https://www.parlamentodeandalucia.es/webdinamica/portal-web-parlamento/utilidades/sindicacionrss.do?contenido=Bopa',
                    ]),
                ],
            ],
        ];
    }

    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/recursosdeinformacion\/coleccionlegislativa\.do/')) {
            $this->getPostData($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function getPostData(PageCrawl $pageCrawl): void
    {
        $postData = [
            'method' => 'POST',
            'options' => [
                'form_params' => [
                    'tipo' => '0',
                    'seleccion' => 'aprobadasen',
                    'legislatura' => '0',
                    'seleccionyo' => 'y',
                    'accion' => 'Ver leyes',
                    'sinpaginacion' => 1,
                ],
            ],
        ];
        $settings = $postData;

        $pageCrawl->setHttpSettings($settings);
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/recursosdeinformacion\/coleccionlegislativa\.do/')) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/utilidades\/sindicacionrss\.do\?contenido=Bopa/')) {
            $this->handleUpdates($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/\/pdf\.do\?tipodoc/')) {
            if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
                $this->handleMeta($pageCrawl);
                $this->arachno->capturePDF($pageCrawl);
            }
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleUpdates(PageCrawl $pageCrawl): void
    {
        $this->arachno->eachX($pageCrawl, '//*[local-name()="item"]',
            function (DOMElement $item) use ($pageCrawl) {
                $domCrawler = new DomCrawler($item);
                $docMetaDto = new DocMetaDto();

                /** @var DOMElement $link */
                $link = $domCrawler->filterXPath('//*[local-name()="link"]')->getNode(0);

                $uniqueId = trim($link->textContent);
                $uniqueId = Str::of($uniqueId)->after('content=');
                $workNumber = Str::of($uniqueId)->after('bp_');
                $fullLink = "https://www.parlamentodeandalucia.es/webdinamica/portal-web-parlamento/pdf.do?tipodoc=bopa&id={$workNumber}";

                /** @var DOMElement $title */
                $title = $domCrawler->filterXPath('//*[local-name()="title"]')->getNode(0);
                $title = trim(explode('-', $title->textContent)[0]);

                $docMetaDto->source_unique_id = 'updates_' . $uniqueId;
                $docMetaDto->work_number = $workNumber;
                $docMetaDto->title = $title;
                $docMetaDto->source_url = $fullLink;
                $docMetaDto->download_url = $fullLink;
                $docMetaDto->language_code = 'spa';
                $docMetaDto->primary_location = '164105';
                $docMetaDto->work_type = 'gazette';

                /** @var DOMElement $workDate */
                $workDate = $domCrawler->filterXPath('//*[local-name()="pubDate"]')->getNode(0);
                $workDate = $workDate->textContent;

                try {
                    $workDate = Carbon::parse($workDate);
                    $docMetaDto->work_date = $workDate;
                } catch (InvalidFormatException) {
                }

                $link = new UrlFrontierLink(['url' => $fullLink]);
                $link->_metaDto = $docMetaDto;
                $this->arachno->followLink($pageCrawl, $link, true);
            });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->eachX($pageCrawl, '//*[@id="main"]/div/div/ul/li/a',
            function (DOMElement $links) use ($pageCrawl) {
                $domCrawler = new DomCrawler($links);

                /** @var DOMElement $anchorElem */
                $anchorElem = $domCrawler->filterXPath('.//a')->getNode(0);
                $href = 'https://www.parlamentodeandalucia.es' . $anchorElem->getAttribute('href');

                $title = Str::of($anchorElem->textContent)->before('(');
                preg_match('/Decreto|Ley|Reglamento/', $title, $matches);
                $uniqueId = $matches[0] . '_' . Str::of($href)->after('id=')->before('&');

                $catDoc = new CatalogueDoc([
                    'title' => $title,
                    'source_unique_id' => $uniqueId,
                    'view_url' => $href,
                    'start_url' => $href,
                    'language_code' => 'spa',
                    'primary_location_id' => '164105',
                ]);

                $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

                $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
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
            'decreto' => 'decree',
            'reglamento' => 'regulation',
            default => 'law'
        };
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc
     */
    protected function handleMeta(PageCrawl $pageCrawl): Doc
    {
        /** @var CatalogueDoc $catalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;

        $workNumber = Str::of((string) $catalogueDoc->source_unique_id)->afterLast('_');

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'spa');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '164105');
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $workNumber);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->start_url);

        preg_match('/Decreto|Ley|Reglamento/', (string) $catalogueDoc->title, $matches);

        $workType = $matches[0];
        $workType = $this->getWorkType($workType);

        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $workType);

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);

        return $doc;
    }
}
