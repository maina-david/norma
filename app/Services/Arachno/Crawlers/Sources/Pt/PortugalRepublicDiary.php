<?php

namespace App\Services\Arachno\Crawlers\Sources\Pt;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use App\Stores\Corpus\ContentResourceStore;
use DOMElement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @codeCoverageIgnore
 * slug: pt-diariodarepublica
 * title: Portugal National Republic Diary
 * url: https://diariodarepublica.pt
 * The .AnonymousCSRFToken that is used in the 'headers' as 'X-Csrftoken' is hard coded in this endpoint https://diariodarepublica.pt/dr/scripts/OutSystems.js?5X3ZVRM+Rn2sb_x7+4GgkQ
 */
class PortugalRepublicDiary extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'pt-diariodarepublica',
            'throttle_requests' => 200,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://diariodarepublica.pt/dr/screenservices/dr/LegislacaoConsolidada/LegCons_Resultado/DataActionPesquisaElastic?Trabalho',
                    ]),
                    new UrlFrontierLink(['url' => 'https://diariodarepublica.pt/dr/screenservices/dr/LegislacaoConsolidada/LegCons_Resultado/DataActionPesquisaElastic?Urbanismo']),
                    new UrlFrontierLink(['url' => 'https://diariodarepublica.pt/dr/screenservices/dr/LegislacaoConsolidada/LegCons_Resultado/DataActionPesquisaElastic?Energia']),
                    new UrlFrontierLink(['url' => 'https://diariodarepublica.pt/dr/screenservices/dr/LegislacaoConsolidada/LegCons_Resultado/DataActionPesquisaElastic?Ambiente']),
                    new UrlFrontierLink(['url' => 'https://diariodarepublica.pt/dr/screenservices/dr/LegislacaoConsolidada/LegCons_Resultado/DataActionPesquisaElastic?Transportes']),
                    new UrlFrontierLink(['url' => 'https://diariodarepublica.pt/dr/screenservices/dr/LegislacaoConsolidada/LegCons_Resultado/DataActionPesquisaElastic?Saúde']),
                ],

                'type_' . CrawlType::FOR_UPDATES->value => [
                    new UrlFrontierLink(['url' => 'https://diariodarepublica.pt/dr/screenservices/dr/Home/Serie1/DataActionGetDataAndApplicationSettings']),
                ],
            ],
        ];
    }

    public function preFetch(PageCrawl $pageCrawl): void
    {
        $modResponse = Http::get(sprintf('https://diariodarepublica.pt/dr/moduleservices/moduleversioninfo?%s', now()->getTimestampMs()));
        $moduleVersion = $modResponse->json()['versionToken'];

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $pageCrawl->setHttpSettings($this->getPostData($pageCrawl, $moduleVersion));
        }

        // get content .OSBlockWidget
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => true,
                    'wait_browser' => 'networkidle0',
                    'wait_for' => '.OSBlockWidget',
                ],
            ]);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/diariodarepublica\.pt\/dr\/screenservices\/dr\/Home\/Serie1\/DataActionGetDataAndApplicationSettings/')) {
            $pageCrawl->setHttpSettings($this->handleInitialUpdateSettings($moduleVersion));
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/screenservices\/dr\/Legislacao_Conteudos\/Conteudo_Detalhe\/DataActionGetConteudoDataAndApplicationSettings/')) {
            $pageCrawl->setHttpSettings($this->handleUpdateSettings($pageCrawl, $moduleVersion));
        }
    }

    /**
     * @param string $url
     * @param string $content
     *
     * @return string
     */
    protected function getApiVersion(string $url, string $content): string
    {
        $apiResponse = Http::get($url);
        $apiVersion = $apiResponse->body();
        preg_match($content, $apiVersion, $matches);
        $apiVersion = str_replace('"', '', $matches[1]);

        return explode(',', $apiVersion)[2];
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $moduleVersion
     *
     * @return array<string, mixed>
     */
    protected function getPostData(PageCrawl $pageCrawl, string $moduleVersion): array
    {
        $url = urldecode($pageCrawl->pageUrl->url);
        $group = Str::of($url)->after('?');

        $content = '/(controller\.callDataAction\("DataActionPesquisaElastic",\s+"screenservices\/dr\/LegislacaoConsolidada\/LegCons_Resultado\/DataActionPesquisaElastic",.+)/';

        $apiVersion = trim($this->getApiVersion('https://diariodarepublica.pt/dr/scripts/dr.LegislacaoConsolidada.LegCons_Resultado.mvc.js?e6uOMn6SPfpfw0yS1A+M2w', $content));

        return [
            'method' => 'POST',
            'options' => [
                'headers' => [
                    'X-Csrftoken' => 'T6C+9iB49TLra4jEsMeSckDMNhQ=',
                    'Content-Type' => 'application/json; charset=UTF-8',
                ],
                'json' => [
                    'versionInfo' => [
                        'moduleVersion' => $moduleVersion,
                        'apiVersion' => $apiVersion,
                    ],
                    'viewName' => 'LegislacaoConsolidada.LegCons_Resultado',
                    'screenData' => [
                        'variables' => [
                            'AreaTematica' => $group,
                            'StartIndex' => 0,
                            'MaxRecords' => 250,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string $moduleVersion
     *
     * @return array<string, mixed>
     */
    protected function handleInitialUpdateSettings(string $moduleVersion): array
    {
        $date = now()->format('Y-m-d');

        $content = '/(controller\.callDataAction\("DataActionGetDataAndApplicationSettings",\s+"screenservices\/dr\/Home\/Serie1\/DataActionGetDataAndApplicationSettings",.+)/';

        $apiVersion = trim($this->getApiVersion('https://diariodarepublica.pt/dr/scripts/dr.Home.Serie1.mvc.js?wrRE2pE6WNbdts40kq1LBQ', $content));

        return [
            'method' => 'POST',
            'options' => [
                'headers' => [
                    'X-Csrftoken' => 'T6C+9iB49TLra4jEsMeSckDMNhQ=',
                ],
                'json' => [
                    'versionInfo' => [
                        'moduleVersion' => $moduleVersion,
                        'apiVersion' => $apiVersion,
                    ],
                    'viewName' => 'Home.home',
                    'screenData' => [
                        'variables' => [
                            'DataSelecionada' => $date,
                            //                            'DataSelecionada' => "2024-06-04",
                            '_dataSelecionadaInDataFetchStatus' => 1,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     * @param string    $moduleVersion
     *
     * @return array<string, mixed>
     */
    protected function handleUpdateSettings(PageCrawl $pageCrawl, string $moduleVersion): array
    {
        $year = now()->format('Y');
        $num = Str::of($pageCrawl->pageUrl->anchor_text ?? '')->before('-');
        $id = Str::of($pageCrawl->pageUrl->anchor_text ?? '')->afterLast('-');

        $content = '/(controller\.callDataAction\("DataActionGetConteudoDataAndApplicationSettings",\s+"screenservices\/dr\/Legislacao_Conteudos\/Conteudo_Detalhe\/DataActionGetConteudoDataAndApplicationSettings",.+)/';
        $apiVersion = trim($this->getApiVersion('https://diariodarepublica.pt/dr/scripts/dr.Legislacao_Conteudos.Conteudo_Detalhe.mvc.js?nXcA+LPSbFWoBP36zGJiwQ', $content));

        return [
            'method' => 'POST',
            'options' => [
                'headers' => [
                    'X-Csrftoken' => 'T6C+9iB49TLra4jEsMeSckDMNhQ=',
                ],
                'json' => [
                    'versionInfo' => [
                        'moduleVersion' => $moduleVersion,
                        'apiVersion' => $apiVersion,
                    ],
                    'viewName' => 'Legislacao_Conteudos.Conteudo_Detalhe',
                    'screenData' => [
                        'variables' => [
                            'DipLegisId' => $id,
                            'ConteudoId1' => $id,
                            'Numero' => $num,
                            'Year' => $year,
                            'Key' => $pageCrawl->pageUrl->anchor_text,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/screenservices\/dr\/LegislacaoConsolidada\/LegCons_Resultado\/DataActionPesquisaElastic\?/')) {
                $this->handleCatalogue($pageCrawl);
            }
            $this->fetchExtraDoc($pageCrawl);
            $this->fetchTitlesNotFound($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $this->handleMeta($pageCrawl);
            $this->captureContent($pageCrawl);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/diariodarepublica\.pt\/dr\/screenservices\/dr\/Home\/Serie1\/DataActionGetDataAndApplicationSetting/')) {
                $this->fetchInitialLink($pageCrawl);
            }

            if ($this->arachno->matchUrl($pageCrawl, '/screenservices\/dr\/Legislacao_Conteudos\/Conteudo_Detalhe\/DataActionGetConteudoDataAndApplicationSettings/')) {
                $this->handleUpdates($pageCrawl);
            }
        }

        if ($this->arachno->matchUrl($pageCrawl, '/.pdf/')) {
            $pageCrawl->setOcrSettings(['languages' => ['por']]);
            $this->arachno->capturePDF($pageCrawl);
        }
    }

    private function readCSV(): Collection
    {
        $handle = fopen(__DIR__ . '/titles_not_found.csv', 'r');

        if ($handle === false) {
            return collect();
        }

        $collection = collect();

        while (($data = fgetcsv($handle)) !== false) {
            $collection->push($data);
        }

        fclose($handle);

        return $collection->filter()->values();
    }

    protected function fetchTitlesNotFound(PageCrawl $pageCrawl): void
    {
        $rows = $this->readCSV();

        $header = collect($rows->shift());

        $title = $header->search('title');
        $url = $header->search('link');
        $type = $header->search('type');

        foreach ($rows as $row) {
            $uniqueId = Str::of($row[$url])->after('.pt/dr/');
            $workType = Str::of($row[$type])->before('n.º');
            $metDt['work_type'] = $this->getWorkType(trim($workType));

            $meta = [
                'title' => $row[$type] . ' - ' . $row[$title],
                'uniqueId' => $uniqueId,
                'url' => $row[$url],
            ];

            $catDoc = $this->arachno->createCatalogueDoc($pageCrawl, $this->createCatDoc($meta));

            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catDoc->id], ['doc_meta' => $metDt]);
        }
    }

    /**
     * @param array<string> $meta
     *
     * @return CatalogueDoc
     */
    protected function createCatDoc(array $meta): CatalogueDoc
    {
        return new CatalogueDoc([
            'title' => $meta['title'],
            'source_unique_id' => $meta['uniqueId'],
            'start_url' => $meta['url'],
            'view_url' => $meta['url'],
            'primary_location_id' => 181914,
            'language_code' => 'por',
            'summary' => $meta['summary'] ?? null,
        ]);
    }

    /**
     * @return void
     */
    protected function fetchExtraDoc(PageCrawl $pageCrawl): void
    {
        $meta = [
            'title' => 'Lei n.º 7/2009 - Labor Code - CT',
            'uniqueId' => 'lei/2009-34546475',
            'url' => 'https://diariodarepublica.pt/dr/legislacao-consolidada/lei/2009-34546475',
        ];

        $this->arachno->createCatalogueDoc($pageCrawl, $this->createCatDoc($meta));
    }

    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $meta = [];
        $data = $pageCrawl->getJson()['data']['Resultados'] ?? '';
        $data = json_decode($data, true);
        $hits = $data['hits']['hits'];

        foreach ($hits as $hit) {
            $type = trim($hit['_source']['consolidacaoType']);
            $year = $hit['_source']['ano'];
            $dbId = $hit['_source']['dbId'];
            $summary = $hit['_source']['sumario'];
            $typeUri = str_replace(' ', '-', strtolower($type));
            $url = "https://diariodarepublica.pt/dr/legislacao-consolidada/{$typeUri}/{$year}-{$dbId}";
            $uniqueId = $hit['_source']['id'];

            $type = $this->getWorkType($hit['_source']['consolidacaoType']);
            $meta['work_type'] = $type;

            $metaData = [
                'title' => $hit['_source']['title'],
                'uniqueId' => $uniqueId,
                'url' => $url,
                'summary' => $summary,
            ];

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $this->createCatDoc($metaData));

            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catalogueDoc->id], ['doc_meta' => $meta]);
        }
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getWorkType(string $type): string
    {
        return match (strtolower($type)) {
            'decreto de aprovação da constituição', 'decreto-lei' => 'decree',
            'portaria' => 'ordinance',
            'resolução', 'resolução do conselho de ministros' => 'resolution',
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
        $catalogueDoc->load('docMeta');

        /** @var DOMElement|null $summary */
        $summary = $pageCrawl->domCrawler->filter('.texto_sumario.int-links')->getNode(0);
        $summary = $summary?->textContent;

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'por');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 181914);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'summary', $catalogueDoc->summary ?? $summary);

        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $catalogueDoc->docMeta?->doc_meta['work_type'] ?? 'law');

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);

        return $doc;
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function fetchInitialLink(PageCrawl $pageCrawl): void
    {
        $list = $pageCrawl->getJson()['data']['DiarioByDiaList']['List'];

        foreach ($list as $item) {
            $innerList = $item['DiplomaLegiList']['List'];
            foreach ($innerList as $inner) {
                $num = $inner['Numero'];
                $num = explode('/', $num);
                $num = implode('-', $num);
                $dbId = $inner['DbId'];

                $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => "https://diariodarepublica.pt/dr/screenservices/dr/Legislacao_Conteudos/Conteudo_Detalhe/DataActionGetConteudoDataAndApplicationSettings?{$num}-{$dbId}", 'anchor_text' => "{$num}-{$dbId}"]), true);
            }
        }
    }

    protected function handleUpdates(PageCrawl $pageCrawl): void
    {
        $docMetaDto = new DocMetaDto();
        $json = $pageCrawl->getJson();
        $json = $json['data']['DetalheConteudo'];
        $uniqueId = Str::of($json['VersaoPDF'])->after('.pt/')->before('.pdf');

        $docMetaDto->title = $json['Titulo'];
        $docMetaDto->source_unique_id = "updates_{$uniqueId}";
        $docMetaDto->work_number = $json['Numero'];
        $docMetaDto->summary = $json['Sumario'];
        $docMetaDto->source_url = $json['VersaoPDF'];
        $docMetaDto->publication_number = $json['DataPublicacao'];
        $docMetaDto->work_type = $this->getWorkType($json['TipoDiploma']);
        $docMetaDto->language_code = 'por';
        $docMetaDto->primary_location = '181914';
        $docMetaDto->download_url = $json['VersaoPDF'];

        $link = new UrlFrontierLink(['url' => $docMetaDto->source_url]);
        $link->_metaDto = $docMetaDto;
        $this->arachno->followLink($pageCrawl, $link, true);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function captureContent(PageCrawl $pageCrawl): void
    {
        $bodyQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'div[data-block="LegislacaoConsolidada.DiplomaCompleto"], .tableIndice',
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
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[@rel="stylesheet"]',
            ],
        ]);

        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => '.OSInline.ThemeGrid_MarginGutter',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => '.icon',
            ],
        ]);

        $preStore = function (Crawler $crawler) {
            $extraExtLinks = $crawler->filter('a:not([href$=".pdf"])');

            /** @var DOMElement $extraExtLink */
            foreach ($extraExtLinks as $extraExtLink) {
                $extraExtLink->removeAttribute('href');
            }

            $externalLinks = $crawler->filter('a.linkfile, a[href*=".pdf"]');

            if ($externalLinks->count() > 0) {
                $contentResourceStore = app(ContentResourceStore::class);

                /** @var DOMElement $externalLink */
                foreach ($externalLinks as $externalLink) {
                    $href = $externalLink->getAttribute('href');

                    $pdf = retry(4, function () use ($href) {
                        return Http::timeout(3000)->get($href);
                    }, 3000);
                    //                    $pdf = Http::timeout(2500)->get($href);
                    $resource = $contentResourceStore->storeResource($pdf, 'application/pdf');
                    $newHref = $contentResourceStore->getLinkForResource($resource);
                    $externalLink->setAttribute('href', $newHref);

                    //                    $pdf = Http::get($href);
                    //                    $resource = $contentResourceStore->storeResource($pdf, 'image/png');
                    //                    $newHref = $contentResourceStore->getLinkForResource($resource);
                    //                    $externalLink->setAttribute('href', $newHref);
                }
            }

            $icons = $crawler->filter('.icon.fa.fa-times.fa-1x');

            /** @var DOMElement|null $icon */
            foreach ($icons as $icon) {
                $icon?->remove();
            }

            /** @var DOMElement|null $btnToRemove */
            $btnToRemove = $crawler->filter('.btn.btn-primary.Scroll')->getNode(0);
            $btnToRemove?->parentElement?->remove();

            return $crawler;
        };

        $this->arachno->capture($pageCrawl, $bodyQueries, $headQueries, $removeQueries, preStoreCallable: $preStore);
    }
}
