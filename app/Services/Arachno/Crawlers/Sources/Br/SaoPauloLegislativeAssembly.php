<?php

namespace App\Services\Arachno\Crawlers\Sources\Br;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use Illuminate\Support\Facades\Http;
use Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 * slug: br-al-sp
 * title: Sao Paulo Legislative Assembly
 * url: https://www.al.sp.gov.br
 */
class SaoPauloLegislativeAssembly extends AbstractCrawlerConfig
{
    /**
     * @param string $url
     *
     * @return int
     */
    protected function findLastPage(string $url): int
    {
        $response = Http::get($url);
        $crawler = new DomCrawler();
        $crawler->addHtmlContent($response->body());

        /** @var DOMElement|null $lastPageElem */
        $lastPageElem = $crawler->filter('ul.pagination li:last-child button')->getNode(0);
        $lastPage = $lastPageElem?->getAttribute('value') ?? 0;

        return (int) $lastPage;
    }

    public function getCrawlerSettings(): array
    {
        $pattern = 'https://www.al.sp.gov.br/norma/resultados?page=%d&size=1000&tipoPesquisa=E&idsTipoNorma=3&_idsTipoNorma=on&idsTipoNorma=28&_idsTipoNorma
                        =on&idsTipoNorma=14&_idsTipoNorma=on&idsTipoNorma=25&_idsTipoNorma=on&idsTipoNorma=9&_idsTipoNorma=on&_idsTipoNorma=on&idsTipoNorma=1&_idsTipoNorma
                        =on&idsTipoNorma=2&_idsTipoNorma=on&_idsTipoNorma=on&_idsTipoNorma=on&_idsTipoNorma=on&_idsTipoNormaon&_idsTipoNorma=on&_idsTipoNorma=on&palavra
                        ChaveEscape=&palavraChave=&_idsTema=1&_idsAutorPropositura=1&nuNorma=&complemento=&ano=&dtNormaInicio=&dtNormaFim=&idTipoSituacao=&buscaLivreEscape
                        =&buscaLivre=&_temQuestionamentos=on&_pesquisa';

        $lastPage = $this->findLastPage(sprintf($pattern, 0));

        $urls = [];

        for ($i = 0; $i <= $lastPage; $i++) {
            $urls[] = new UrlFrontierLink(['url' => sprintf($pattern, $i)]);
        }

        return [
            'slug' => 'br-al-sp',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => $urls,
            ],
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function updatePageEncoding(PageCrawl $pageCrawl): void
    {
        // Let us fix the encoding of the document.
        $encoding = $pageCrawl->domCrawler->getNode(0)?->ownerDocument->encoding ?? 'UTF-8';
        $pageCrawl->domCrawler->clear();
        $pageCrawl->domCrawler->addHtmlContent($pageCrawl->contentCache->response_body ?? '', $encoding);
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/al\.sp\.gov\.br\/norma\/resultados\?/')) {
            $this->updatePageEncoding($pageCrawl);
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $this->updatePageEncoding($pageCrawl);
            $this->handleMeta($pageCrawl);
            $this->handleCapture($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, '#resultados tbody tr', function (DOMElement $row) use ($pageCrawl) {
            $domCrawler = new DomCrawler($row, $pageCrawl->pageUrl->url, 'https://www.al.sp.gov.br');
            $meta = [];

            /** @var DOMElement $summaryElem */
            $summaryElem = $domCrawler->filterXPath('.//td[2]')->getNode(0);
            $meta['summary'] = $summaryElem->textContent;

            $url = $domCrawler->filterXPath('.//td//a[contains(@href, ".html")]')->getNode(0)
                ? $domCrawler->filterXPath('.//td//a[contains(@href, ".html")]')->link()->getUri()
                : '';

            /** @var DOMElement $textElem */
            $textElem = $domCrawler->filterXPath('//td/a[@class="link_norma"]')->getNode(0);
            $title = $textElem->textContent;
            $uniqueId = Str::of($url)->after('legislacao/')->before('.html');
            $workDate = trim(Str::of($title)->after(', de'));

            $workType = trim(Str::of($url)->after('legislacao/')->before('/'));
            $meta['work_type'] = $workType;

            $catDoc = new CatalogueDoc([
                'title' => $title,
                'source_unique_id' => $uniqueId,
                'view_url' => $url,
                'start_url' => $url,
                'primary_location_id' => '79386',
                'language_code' => 'por',
            ]);

            try {
                $workDate = str_replace('/', '-', $workDate);
                $workDate = Carbon::parse($workDate);
                $meta['work_date'] = $workDate;
            } catch (InvalidFormatException) {
            }

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);

            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], [
                'doc_meta' => $meta,
            ]);

            $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
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
        $catalogueDoc->load('docMeta');

        $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'por');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '79386');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'summary', $catalogueDoc->docMeta?->doc_meta['summary'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $catalogueDoc->docMeta?->doc_meta['work_date'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $catalogueDoc->docMeta?->doc_meta['work_date'] ?? null);

        $workType = $catalogueDoc->docMeta?->doc_meta['work_type'] ?? '';
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $this->getWorkType($workType));

        $this->arachno->saveDoc($pageCrawl);
    }

    /**
     * @param string $workType
     *
     * @return string
     */
    protected function getWorkType(string $workType): string
    {
        return match (strtolower($workType)) {
            'resolução' => 'resolution',
            'decreto.lei', 'decreto.lei.complementar', 'decreto.legislativo','decreto', 'decreto.legislativo' => 'decree',
            default => 'law'
        };
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCapture(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'body',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            //            [
            //                'type' => DomQueryType::XPATH,
            //                'query' => '//head/meta[@property="og:title"]',
            //            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//style',
            ],
        ]);

        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'center a[target="_parent"]',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'a[href="https://www.al.sp.gov.br"]',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'a[href="http://www.al.sp.gov.br"]',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'a[href*="norma/?id"]',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'a[href*="norma/?id"]',
            ],
        ]);

        $preStore = function (DomCrawler $crawler) {
            foreach ($crawler->filter('a[href*="norma"]') as $anchorElem) {
                /** @var DOMElement $anchorElem */
                if (str_contains($anchorElem->textContent, 'Ficha informativa')) {
                    $anchorElem->remove();
                }
            }
            foreach ($crawler->filter('a[class="anotacao-link"], a[href*="norma"]') as $anchor) {
                /** @var DOMElement $anchor */
                $anchor->removeAttribute('href');
            }

            return $crawler;
        };

        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, $removeQueries, preStoreCallable: $preStore);
    }
}
