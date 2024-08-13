<?php

namespace App\Services\Arachno\Crawlers\Sources\Cr;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Stores\Corpus\ContentResourceStore;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use DOMNode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 * slug: cr-pgrweb-go
 * title: Costa Rican legal information system
 * url: https://www.pgrweb.go.cr/
 */
class CostaRicaInformationSystem extends AbstractCrawlerConfig
{
    /** @var array<string> */
    protected array $workTypes = ['ley', 'acuerdo', 'decreto ejecutivo', 'directriz', 'reglamento', 'resolución', 'decreto'];

    /**
     * @return array<string>
     */
    protected function getStartLinks(): array
    {
        $startUrls = [];
        $lawBranches = [50, 49, 59, 55, 40, 39, 56, 58, 57, 53, 1,
            4, 27, 2, 3, 5, 6, 7, 8, 9, 11, 51, 18, 19, 28, 29, 10,
            12, 26, 35, 22, 13, 32, 14, 24, 31, 15, 16, 17, 37, 38,
            33, 34, 30, 20, 23, 52, 21, 61, 25, 60, 43, 41, 42, 46,
            48, 47, 44, 45];
        foreach ($lawBranches as $lawBranch) {
            $startUrls[] = new UrlFrontierLink([
                'url' => "https://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_temas.aspx?lawBranch={$lawBranch}",
            ]);
        }

        return $startUrls;
    }

    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'cr-pgrweb-go',
            'throttle_requests' => 500,
            'start_urls' => [
                'type_' . CrawlType::FULL_CATALOGUE->value => [
                    ...$this->getStartLinks(),
                ],
            ],
        ];
    }

    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/image/')) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => ['render_js' => false],
            ]);
        }

        //        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
        //            $compressedImages = $pageCrawl->domCrawler->filter('img[src$="emz"]');
        //
        //            if ($compressedImages->count() > 0) {
        //            }
        //        }

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            $url = $pageCrawl->getFinalRedirectedUrl();

            $branchNum = Str::of($url)->after('lawBranch=');
            $settings = [
                'method' => 'POST',
                'options' => [
                    'form_params' => $this->getInitialPayload($pageCrawl, $branchNum),
                    'headers' => [
                        'User-Agent' => $pageCrawl->getSettingUserAgent(),
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9',
                        'Referer' => 'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_temas.aspx',
                    ],
                ],
            ];

            $pageCrawl->setHttpSettings($settings);
        }
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     * @param string                                   $branchNum
     *
     * @return array<string|int|null>
     */
    protected function getInitialPayload(PageCrawl $pageCrawl, string $branchNum): array
    {
        $response = Http::accept('text/html,application/xhtml+xml,application/xml;q=0.9')
            ->get('https://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_temas.aspx');
        $crawler = new Crawler($response->body());
        /** @var DOMElement $viewState */
        $viewState = $crawler->filter('#__VIEWSTATE')->getNode(0);
        $viewState = $viewState->getAttribute('value');

        /** @var DOMElement $eventValidation */
        $eventValidation = $crawler->filter('#__EVENTVALIDATION')->getNode(0);
        $eventValidation = $eventValidation->getAttribute('value');

        $this->arachno->setCrawlCookies($pageCrawl, $response->cookies);

        return [
            '_ctl0__ctl0_ToolkitScriptManager1_HiddenField' => null,
            '__EVENTTARGET' => null,
            '__EVENTARGUMENT' => null,
            '__VIEWSTATE' => $viewState,
            '__VIEWSTATEGENERATOR' => '8F32875B',
            '__EVENTVALIDATION' => $eventValidation,
            '_ctl0:_ctl0:ContentPlaceHolder1:txtConsulta' => null,
            '_ctl0:_ctl0:ContentPlaceHolder1:Criterio' => 'RBNormativa',
            '_ctl0:_ctl0:ContentPlaceHolder1:txtAnno' => null,
            '_ctl0:_ctl0:ContentPlaceHolder1:DDLCantiadadResultados' => '50',
            '_ctl0:_ctl0:ContentPlaceHolder1:DDLBuscarEn' => 'Ficha',
            '_ctl0:_ctl0:ContentPlaceHolder1:ContentPlaceHolderMenuBusqueda:ddRamaDerecho' => $branchNum,
            '_ctl0:_ctl0:ContentPlaceHolder1:ContentPlaceHolderMenuBusqueda:txtNombre' => null,
            '_ctl0:_ctl0:ContentPlaceHolder1:ContentPlaceHolderMenuBusqueda:cmdConsultar' => 'Buscar',
        ];
    }

    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->matchUrl($pageCrawl, '/\/scij\/Busqueda\/Normativa\/Normas\/nrm_temas/')) {
            $response = Http::withoutRedirecting()
                ->accept('text/html,application/xhtml+xml,application/xml;q=0.9')
                ->get('https://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_temas.aspx');

            $this->arachno->setCrawlCookies($pageCrawl, $response->cookies);
        }

        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/\/scij\/Busqueda\/Normativa\/Normas\/nrm_temas/')) {
                $this->handleCatalogue($pageCrawl);
            }
            $this->followToContent($pageCrawl);
        }

        if ($this->arachno->matchUrl($pageCrawl, '/Normas\/nrm_texto_completo\.aspx/')) {
            $this->fetchUrlMeta($pageCrawl);
        }

        if ($this->arachno->crawlIsFetchWorks($pageCrawl)
            && $this->arachno->matchUrl($pageCrawl, '/Normas\/nrm_texto_completo\.aspx/')) {
            $this->handleMeta($pageCrawl);
            $this->handleContent($pageCrawl);
        }
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'table td a[href*="nrm_texto_completo"]',
            function (DOMElement $element) use ($pageCrawl) {
                $crawler = new Crawler($element);

                /** @var DOMNode $row */
                $row = $crawler->filterXPath('.//a')->getNode(0);
                $row = $row?->parentNode->parentNode ?? '';
                /** @var DOMElement $row */
                $title = $row->previousElementSibling;
                $title = trim($title?->textContent ?? '');
                $title = preg_replace('/\n/', ' ', $title);

                /** @var DOMElement $href */
                $href = $crawler->filterXPath('.//a')->getNode(0);
                $href = $href->getAttribute('href');

                $uniqueId = Str::of($href)->after('Valor2=');

                $catDoc = new CatalogueDoc([
                    'title' => $title,
                    'source_unique_id' => $uniqueId,
                    'start_url' => $href,
                    'view_url' => $href,
                    'primary_location_id' => 172883,
                    'language_code' => 'spa',
                ]);

                $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
                $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
            });
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function followToContent(PageCrawl $pageCrawl): void
    {
        $urls = [
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=54836&nValor3=114966&strTipM=TC ',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?nValor1=1&nValor2=48141',
            'https://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?nValor1=1&nValor2=76879',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=88120&nValor3=114959&strTipM=TC',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=70605&nValor3=85349&strTipM=TC',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=67113&nValor3=79388&strTipM=TC',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?nValor1=1&nValor2=82487',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?nValor1=1&nValor2=59524',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?nValor1=1&nValor2=68467',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=56415&nValor3=61836&strTipM=TC',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=67850&nValor3=80550&strTipM=TC',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=24436&nValor3=94042&param2=1&strTipM=TC&lResultado=2&strSim=simp',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=86628&nValor3=112489&strTipM=TC',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=91043&nValor3=120129&strTipM=TC',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?nValor1=1&nValor2=29536',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?nValor1=1&nValor2=65395',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=26421&nValor3=96838&param2=1&strTipM=TC&lResultado=5&strSim=simp',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=21014&nValor3=22336&strTipM=TC',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=7439&nValor3=7974&strTipM=TC',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?nValor1=1&nValor2=88075',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=60478&nValor3=68183&strTipM=TC',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=29498&nValor3=104139&strTipM=TC',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?nValor1=1&nValor2=12648',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=35957&nValor3=37908&strTipM=TC',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=7591&nValor3=8139&strTipM=TC',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?nValor1=1&nValor2=62896',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=96890&nValor3=130030&strTipM=TC',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?nValor1=1&nValor2=53003',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?nValor1=1&nValor2=80715',
            //           Duplicate 'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=24436&nValor3=94042&param2=1&strTipM=TC&lResultado=2&strSim=simp',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=24319&nValor3=25739&strTipM=TC',
            'http://www.pgrweb.go.cr/scij/Busqueda/Normativa/Normas/nrm_texto_completo.aspx?param1=NRTC&nValor1=1&nValor2=6825&nValor3=7296&strTipM=TC',
        ];

        foreach ($urls as $url) {
            $this->arachno->followLink($pageCrawl, new UrlFrontierLink(['url' => $url]));
        }
    }

    /**
     * @param \App\Services\Arachno\Frontier\PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function fetchUrlMeta(PageCrawl $pageCrawl): void
    {
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $url = $pageCrawl->pageUrl->url;

        if ($pageCrawl->domCrawler->filter('#dvContenedorTextoCompleto')->getNode(0)) {
            $pageCrawl->domCrawler->filter('');
            $title = $pageCrawl->domCrawler->filter('#_ctl0__ctl0_ContentPlaceHolder1_ContentPlaceHolderMenuBusqueda_lblTitulo')->text();
            $uniqueId = Str::of($url)->after('Valor2=');

            $type = $pageCrawl->domCrawler->filter('#SubPanelDondeEstoy')->text();
            $workType = array_values(array_intersect(array_map('strtolower', explode(' ', $type)), $this->workTypes))[0] ?? '';
            $workType = $this->getWorkType($workType);

            $catDoc = new CatalogueDoc([
                'title' => $title,
                'source_unique_id' => $uniqueId,
                'start_url' => $url,
                'view_url' => $url,
                'primary_location_id' => 172883,
                'language_code' => 'spa',
            ]);

            $catalogueDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
            $pageCrawl->pageUrl->catalogue_doc_id = $catalogueDoc->id;
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

        $title = $pageCrawl->domCrawler->filter('#_ctl0__ctl0_ContentPlaceHolder1_ContentPlaceHolderMenuBusqueda_lblTitulo')->text();

        /** @var DOMElement $panelText */
        $panelText = $pageCrawl->domCrawler->filter('#SubPanelDondeEstoy')->getNode(0);
        $panelText = trim($panelText->textContent);
        $workType = array_values(array_intersect(array_map('strtolower', explode(' ', $panelText)), $this->workTypes))[0] ?? '';

        $workType = $this->getWorkType($workType);

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'spa');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 172883);
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->view_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', $catalogueDoc->work_type ?? $workType);

        try {
            $date = Str::of($panelText)->after(' >> Fecha')->before(' >> Texto completo');
            $date = explode('/', trim($date));
            $date = Carbon::parse(implode('-', $date));

            $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $date);
            $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $date);
        } catch (InvalidFormatException) {
        }
        $this->arachno->saveDoc($pageCrawl);
        $pageCrawl->setDoc($doc);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getWorkType(string $type): string
    {
        return match (strtolower($type)) {
            'ley' => 'law',
            'acuerdo' => 'agreement',
            'decreto ejecutivo', 'decreto' => 'decree',
            'directriz' => 'directive',
            'reglamento' => 'regulation',
            'resolución' => 'resolution',
            default => 'act'
        };
    }

    /**
     * @param \Symfony\Component\DomCrawler\Crawler $dom
     *
     * @return void
     */
    protected function removeFromContent(DomCrawler $dom): void
    {
        $removables = $dom->filter('#ficha, #_ctl0__ctl0_ContentPlaceHolder1_ContentPlaceHolderMenuBusqueda_Panel1, #barra, tr td a[href*="up"], a[href*="#ddown"]');

        /** @var DOMElement|null $removable */
        foreach ($removables as $removable) {
            $removable?->remove();
        }

        /** @var DOMElement|null $content */
        $content = $dom->filter('meta[http-equiv="Content-Type"]')->getNode(0);
        $content?->remove();

        $breaks = $dom->filter('br+br');

        /** @var DOMElement|null $break */
        foreach ($breaks as $break) {
            $break?->remove();
        }

        $articleAnchors = $dom->filter('a[class="text"]');

        /** @var DOMElement $articleAnchor */
        foreach ($articleAnchors as $articleAnchor) {
            $article = trim($articleAnchor->textContent);
            if (strtolower($article) === 'ficha articulo') {
                $articleAnchor->remove();
            }
        }

        $extLinks = $dom->filter('a:not([href$="pdf"])');

        /** @var DOMElement $extLink */
        foreach ($extLinks as $extLink) {
            $extLink->removeAttribute('href');
        }
    }

    /**
     * @param \Symfony\Component\DomCrawler\Crawler $dom
     *
     * @return void
     */
    protected function alterContent(DomCrawler $dom): void
    {
        /** @var DOMElement $table */
        $table = $dom->filter('#divTextoPrincipal table:not([class="MsoTableGrid"])')->getNode(0);
        $table->setAttribute('style', 'border-color: transparent !important;');
        $rows = $dom->filter('#divTextoPrincipal > table > tr > td');

        /** @var DOMElement $row */
        foreach ($rows as $row) {
            $row->setAttribute('style', 'border-color: transparent !important;');
        }

        $completeTable = $dom->filter('#dvContenedorTextoCompleto > table  tr > td');

        /** @var DOMElement $tableRow */
        foreach ($completeTable as $tableRow) {
            $tableRow->setAttribute('style', 'border-color: transparent !important;');
        }

        /** @var DOMElement $tdData */
        $tdData = $dom->filter('#divTextoPrincipal td.style1')->getNode(0);
        $tdData->setAttribute('style', 'border-color: transparent !important;');

        /** @var DOMElement|null $contentTitle */
        $contentTitle = $dom->filter('#_ctl0__ctl0_ContentPlaceHolder1_ContentPlaceHolderMenuBusqueda_lblTitulo')->getNode(0);
        /** @var DOMElement|null $contentTitleTd */
        $contentTitleTd = $contentTitle?->parentNode?->parentNode;
        $contentTitleTd?->setAttribute('style', 'text-align: center;border-color: transparent !important;');

        $brokenImages = $dom->filter('img[src^="http://localhost/"]');

        /** @var DOMElement $brokenImage */
        foreach ($brokenImages as $brokenImage) {
            $brokenImage->setAttribute('src', str_replace('http://localhost/', 'http://www.pgrweb.go.cr/', $brokenImage->getAttribute('src')));
        }

        $relTableDts = $dom->filter('table.MsoNormalTable tr td');

        /** @var DOMElement $relTableDt */
        foreach ($relTableDts as $relTableDt) {
            $relTableDt->setAttribute('style', 'border-color: black !important;');
        }

        //        $compressedImages = $dom->filter('img[src$="emz"]');
        //
        //        if ($compressedImages->count() > 0) {
        //            $this->fetchConvertedImages($pageCrawl);
        //        }

        $this->removeFromContent($dom);
    }

    /**
     * @param \Symfony\Component\DomCrawler\Crawler $dom
     *
     * @return void
     */
    protected function addToContentResources(DomCrawler $dom): void
    {
        if ($dom->filter('img[src]')->count() > 0) {
            foreach ($dom->filter('img[src]') as $imgLink) {
                /** @var DOMElement $imgLink */
                $fullUrl = str_contains($imgLink->getAttribute('src'), 'http://www.pgrweb.go.cr')
                    ? $imgLink->getAttribute('src')
                    : "http://www.pgrweb.go.cr{$imgLink->getAttribute('src')}";

                $imLink = retry(4, function () use ($fullUrl) {
                    return Http::timeout(3000)->get($fullUrl);
                }, 3000);

                $contentResourceStore = app(ContentResourceStore::class);

                $resource = $contentResourceStore->storeResource($imLink, 'image/png');
                $newSrc = $contentResourceStore->getLinkForResource($resource);
                $imgLink->setAttribute('src', $newSrc);
            }
        }
    }

    protected function handleContent(PageCrawl $pageCrawl): void
    {
        /** @var DOMElement $pageContent */
        $pageContent = $pageCrawl->domCrawler->filter('#dvContenedorTextoCompleto')->getNode(0);
        $pageContent->removeAttribute('style');
        $pageContent = $pageContent->parentNode;

        $dom = new DomCrawler($pageContent);

        $this->alterContent($dom);

        $this->addToContentResources($dom);

        $this->arachno->captureContent($pageCrawl, $dom->outerHtml());
    }
}
