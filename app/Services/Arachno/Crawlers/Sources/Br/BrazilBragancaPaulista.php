<?php

namespace App\Services\Arachno\Crawlers\Sources\Br;

use App\Enums\Arachno\CrawlType;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\TocItemDraft;
use DOMElement;
use Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 * slug: br-braganca-paulista
 * title: Braganca Paulista, Brazil
 * url: https://camarabp.sp.gov.br/
 */
class BrazilBragancaPaulista extends AbstractCrawlerConfig
{
    protected string $baseDomain = 'https://leismunicipais.com.br';

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'br-braganca-paulista',
            'throttle_requests' => 500,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    new UrlFrontierLink([
                        'url' => 'https://leismunicipais.com.br/camara/sp/bragancapaulista?q=&page=1&types=28&types=4&types=5&types=24&types=10&types=35',
                    ]),
                ],
            ],
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/camara\/sp\/bragancapaulista.*/')) {
            $this->handleCatalogue($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            // a2/sp/b/braganca-paulista
            $this->handleMeta($pageCrawl);
            $this->handleCapture($pageCrawl);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => true,
                    'wait_browser' => 'networkidle0',
                    'wait_for' => 'div#law-content',
                    'premium_proxy' => true,
                ],
            ]);
        }
    }

    // /**
    //  * @param PageCrawl $pageCrawl
    //  *
    //  * @return void
    //  */
    // public function nextPage(PageCrawl $pageCrawl): void
    // {
    //     $links = $pageCrawl->domCrawler->filter('div.row:nth-child(8) ul.pagination li:last-child a');
    //     /** @var DOMElement|null $lastPageElem */
    //     $lastPageElem = $links->getNode(0);
    //     $lastPage = $lastPageElem?->getAttribute('href') ?? '';
    //     if ($lastPage !== '') {
    //         $lastPage = (int) (string) Str::of($lastPage)->after('&page=')->before('&types=');
    //         $page = (int) (string) Str::of($pageCrawl->pageUrl->url)->after('&page=')->before('&types=');
    //         $currentPage = $page;

    //         if ($lastPage && $page < $lastPage) {
    //             $page++;
    //             $pageUrl = (string) Str::of($pageCrawl->pageUrl->url);
    //             $urlSplit1 = explode("page={$currentPage}", $pageUrl)[0];
    //             $urlSplit2 = explode("page={$currentPage}", $pageUrl)[1];
    //             $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => "{$urlSplit1}page={$page}{$urlSplit2}"]));
    //         }
    //     }
    // }

    // /**
    //  * @param PageCrawl $pageCrawl
    //  *
    //  * @return void
    //  */
    // protected function handleCatalogue(PageCrawl $pageCrawl): void
    // {
    //     $this->arachno->each($pageCrawl, 'div.container div.row div.col-md-10 ul.list-laws li a', function (DOMElement $link) use ($pageCrawl) {
    //         $trCrawler = new DomCrawler($link);
    //         $href = $link->getAttribute('href');
    //         $fullLink = "{$this->baseDomain}{$href}";
    //         $id = (string) Str::of($href)
    //             ->after('lei-ordinaria/')
    //             ->beforeLast('/');
    //         $title = $trCrawler->filter('span.title strong')->getNode(0);
    //         $title = trim($title->textContent ?? '');
    //         $catDoc = new CatalogueDoc([
    //             'title' => $title,
    //             'start_url' => $fullLink,
    //             'view_url' => $fullLink,
    //             'source_unique_id' => $id,
    //             'language_code' => 'por',
    //             'primary_location_id' => '79489',
    //         ]);

    //         $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
    //         $this->nextPage($pageCrawl);
    //     });
    // }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        foreach ($this->getDocs() as $id => $doc) {
            $id = $id;
            $title = $doc['title'];
            $startUrl = $doc['start_url'];
            $catDoc = new CatalogueDoc([
                'title' => $title,
                'start_url' => $startUrl,
                'view_url' => $startUrl,
                'source_unique_id' => $id,
                'language_code' => 'por',
                'primary_location_id' => '79489',
            ]);

            $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDocs(): array
    {
        return [
            '/a2/lei-organica-braganca-paulista-sp' => [
                'title' => 'LEI ORGÂNICA DO MUNICÍPIO DE BRAGANÇA PAULISTA/SP.',
                'start_url' => 'https://leismunicipais.com.br/a2/lei-organica-braganca-paulista-sp',
            ],
            '/a2/plano-de-zoneamento-uso-e-ocupacao-do-solo-braganca-paulista-sp' => [
                'title' => 'LEI COMPLEMENTAR Nº 556 de 20 de julho de 2007',
                'start_url' => 'https://leismunicipais.com.br/a2/plano-de-zoneamento-uso-e-ocupacao-do-solo-braganca-paulista-sp',
            ],
            '/a2/plano-diretor-braganca-paulista-sp' => [
                'title' => 'LEI COMPLEMENTAR Nº 893, DE 3 DE JANEIRO DE 2020.',
                'start_url' => 'https://leismunicipais.com.br/a2/plano-diretor-braganca-paulista-sp',
            ],
            'lei-ordinaria/2020/474/4732' => [
                'title' => 'LEI Nº 4.732, DE 26 DE JUNHO DE 2020.',
                'start_url' => 'https://leismunicipais.com.br/a/sp/b/braganca-paulista/lei-ordinaria/2020/474/4732/lei-ordinaria-n-4732-2020-institui-o-sistema-para-a-gestao-sustentavel-de-residuos-da-construcao-civil-e-residuos-volumosos-de-acordo-com-a-resolucao-conama-n-307-2002-e-suas-alteracoes-e-da-outras-providencias',
            ],
            'lei-complementar/2019/89/882' => [
                'title' => 'LEI COMPLEMENTAR 882, de 12 de novembro de 2019.',
                'start_url' => 'https://leismunicipais.com.br/a/sp/b/braganca-paulista/lei-complementar/2019/89/882/lei-complementar-n-882-2019-publicado-na-imprensa-oficial-em-14-e-28-11-2019-pags-5-a-7-e-5-dispoe-sobre-a-regularizacao-de-construcoes-clandestinas-e-irregulares-na-forma-que-especifica-e-da-outras-providencias',
            ],
            'lei-complementar/2019/86/868' => [
                'title' => 'LEI COMPLEMENTAR Nº 868, DE 05 DE JUNHO DE 2019.',
                'start_url' => 'https://leismunicipais.com.br/a/sp/b/braganca-paulista/lei-complementar/2019/86/868/lei-complementar-n-868-2019-institui-os-procedimentos-a-serem-adotados-pelas-concessionarias-de-servicos-publicos-ou-terceiros-interessados-em-obras-e-ou-servicos-executados-nas-vias-e-logradouros-publicos-e-da-outras-providencias',
            ],
            'lei-ordinaria/1999/318/3181' => [
                'title' => 'LEI Nº 3181 de 07 de junho de 1999 (Regulamentada pelo Decreto nº 10778/1999)',
                'start_url' => 'https://leismunicipais.com.br/a/sp/b/braganca-paulista/lei-ordinaria/1999/318/3181/lei-ordinaria-n-3181-1999-dispoe-sobre-limpeza-publica-do-municipio-de-braganca-paulista-e-da-outras-providencias.html',
            ],
            'lei-ordinaria/2015/445/4459' => [
                'title' => 'LEI Nº 4459, DE 13 DE MARÇO DE 2015.(Regulamentada pelo Decreto nº 2158/2015)',
                'start_url' => 'https://leismunicipais.com.br/a/sp/b/braganca-paulista/lei-ordinaria/2015/445/4459/lei-ordinaria-n-4459-2015-dispoe-sobre-a-utilizacao-de-agua-com-uso-de-mangueira-para-irrigacao-de-jardins-e-limpeza-de-veiculos-calcamentos-e-passeios-publicos-no-municipio-de-braganca-paulista.html',
            ],
            'decreto/2014/193/1939' => [
                'title' => 'DECRETO Nº 1939, DE 18 DE SETEMBRO DE 2014',
                'start_url' => 'https://leismunicipais.com.br/a/sp/b/braganca-paulista/decreto/2014/193/1939/decreto-n-1939-2014-declara-imunes-de-corte-as-arvores-que-especifica-e-da-outras-providencias.html',
            ],
            'lei-ordinaria/2011/426/4265' => [
                'title' => 'LEI Nº 4265, de 26 de setembro de 2011.',
                'start_url' => 'https://leismunicipais.com.br/a/sp/b/braganca-paulista/lei-ordinaria/2011/426/4265/lei-ordinaria-n-4265-2011-institui-a-politica-municipal-de-recursos-hidricos-estabelece-normas-e-diretrizes-para-a-recuperacao-a-preservacao-e-a-conservacao-dos-recursos-hidricos-e-cria-o-sistema-municipal-de-gerenciamento-dos-recursos-hidricos.html',
            ],
            'lei-ordinaria/2011/423/4236' => [
                'title' => 'LEI Nº 4236 de 22 de junho de 2011.',
                'start_url' => 'https://leismunicipais.com.br/a/sp/b/braganca-paulista/lei-ordinaria/2011/423/4236/lei-ordinaria-n-4236-2011-dispoe-sobre-a-proibicao-de-queimadas-no-municipio-de-braganca-paulista-estabelece-penalidades-e-da-outras-providencias.html',
            ],
            'decreto/2011/124/1241' => [
                'title' => 'DECRETO Nº 1241 de 27 de junho de 2011',
                'start_url' => 'https://leismunicipais.com.br/a/sp/b/braganca-paulista/decreto/2011/124/1241/decreto-n-1241-2011-dispoe-sobre-a-suspensao-das-analises-de-projetos-de-reforma-revitalizacao-construcao-e-qualquer-outra-intervencao-em-areas-de-preservacao-permanente-ao-longo-dos-rios-e-ribeiroes-existentes-nos-limites-do-municipio.html',
            ],
            'lei-ordinaria/1971/114/1146' => [
                'title' => 'LEI Nº 1146, DE 13/07/1971.',
                'start_url' => 'https://leismunicipais.com.br/a/sp/b/braganca-paulista/lei-ordinaria/1971/114/1146/lei-ordinaria-n-1146-1971-dispoe-sobre-o-codigo-de-obras-e-urbanismo-da-estancia-de-braganca-paulista.html',
            ],
            'lei-ordinaria/2009/404/4049' => [
                'title' => 'LEI Nº 4049, de 29 de julho de 2009',
                'start_url' => 'https://leismunicipais.com.br/a/sp/b/braganca-paulista/lei-ordinaria/2009/404/4049/lei-ordinaria-n-4049-2009-estabelece-diretrizes-criterios-e-normas-para-emissao-de-ruidos-urbanos-e-protecao-do-bem-estar-e-do-sossego-publico.html',
            ],
            'lei-ordinaria/1954/19/198' => [
                'title' => 'LEI Nº 198 - DE 14/12/1954',
                'start_url' => 'https://leismunicipais.com.br/a/sp/b/braganca-paulista/lei-ordinaria/1954/19/198/lei-ordinaria-n-198-1954-dispoe-sobre-demolicao-de-predios.html',
            ],
            'lei-ordinaria/2010/413/4139' => [
                'title' => 'LEI Nº 4139, de 31 de maio de 2010.',
                'start_url' => 'https://leismunicipais.com.br/a/sp/b/braganca-paulista/lei-ordinaria/2010/413/4139/lei-ordinaria-n-4139-2010-dispoe-sobre-medidas-de-controle-da-disseminacao-de-agentes-biologicos-de-doencas-em-estabelecimentos-que-fornecam-alimentos-e-da-outras-providencias.html',
            ],
        ];
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
        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'por');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', '79489');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', 'act');

        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
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
                'query' => 'div#law-content',
            ],
        ]);
        $headQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//title',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//head//link[contains(@href,"main.css") and @rel="stylesheet"]',
            ],
        ]);
        $removeQueries = $this->arrayOfQueries([
            [
                'type' => DomQueryType::CSS,
                'query' => 'div.content',
            ],
            [
                'type' => DomQueryType::XPATH,
                'query' => '//*[@id="law-content"]/p',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'div#consolidacao-modal',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'div#meuMenu',
            ],
            [
                'type' => DomQueryType::CSS,
                'query' => 'div.pull-right[style="color: #d42232;"]',
            ],
        ]);
        $preStore = function (DomCrawler $crawler) use ($pageCrawl) {
            foreach ($crawler->filter('a[href]') as $href) {
                /** @var DOMElement $href */
                $href->removeAttribute('href');
            }
            foreach ($crawler->filter('a[name]') as $anchor) {
                /** @var DOMElement $anchor */
                $tocId = $anchor->getAttribute('name');
                $label = $anchor->textContent ?? '';
                $anchor->setAttribute('id', $tocId);
                $this->arachno->createTocItem($pageCrawl, new TocItemDraft([
                    'source_unique_id' => '#' . $tocId,
                    'href' => '#' . $tocId,
                    'label' => $label,
                    'level' => 1,
                ]));
            }

            return $crawler;
        };
        $this->arachno->capture(
            $pageCrawl,
            $bodyQueries,
            $headQueries,
            $removeQueries,
            preStoreCallable: $preStore,
        );
    }
}
