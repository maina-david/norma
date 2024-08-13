<?php

namespace App\Services\Arachno\Crawlers\Sources\Ma;

use App\Enums\Arachno\CrawlType;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueDocMeta;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DocMetaDto;
use Carbon\Exceptions\InvalidFormatException;
use DOMElement;
use DOMNode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @codeCoverageIgnore
 *
 * slug: ma-arabe-accueil-national
 * title: Morocco Arabe Accueil National
 * url: http://www.sgg.gov.ma
 */
class MoroccoArabeAccueilNational extends AbstractCrawlerConfig
{
    private string $baseDomain = 'http://www.sgg.gov.ma';

    /**
     * The config that is implemented by the crawler.
     *
     * @return array<string, mixed>
     */
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'ma-arabe-accueil-national',
            'throttle_requests' => 400,
            'start_urls' => $this->getArabeMoroccoStartUrls(),
        ];
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/arabe\/textesimportants\.aspx/') || $this->arachno->matchUrl($pageCrawl, '/arabe\/textesconsolides\.aspx/')) {
                $this->handleCatalogue($pageCrawl);
            }
            if ($this->arachno->matchUrl($pageCrawl, '/arabe\/DesktopModules\/MVC\/TableListBO\/BO\/AjaxMethod\?/')) {
                $response = Http::get('http://www.sgg.gov.ma/arabe/DesktopModules/MVC/TableListBO/BO/AjaxMethod?_=1716292079877');
                $this->arachno->setCrawlCookies($pageCrawl, $response->cookies);

                $this->handleGeneralBulletinCatalogue($pageCrawl);
            }

            if ($this->arachno->matchUrl($pageCrawl, '/DesktopModules\/MVC\/TableListBO\/BO\/AjaxMethod\?/')) {
                $response = Http::get('http://www.sgg.gov.ma/DesktopModules/MVC/TableListBO/BO/AjaxMethod?_=1718868434506');
                $this->arachno->setCrawlCookies($pageCrawl, $response->cookies);

                $this->handleGeneralBulletinCatalogue($pageCrawl);
            }
        }
        if ($this->arachno->crawlIsFetchWorks($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/\.pdf/i')) {
                if ($this->arachno->matchUrl($pageCrawl, '/fr\./i')) {
                    $this->handleMeta($pageCrawl);
                    $pageCrawl->setOcrSettings(['languages' => ['fra']]);
                    $this->arachno->capturePDF($pageCrawl);
                }
                $this->handleMeta($pageCrawl);
                $pageCrawl->setOcrSettings(['languages' => ['ara']]);
                $this->arachno->capturePDF($pageCrawl);
            }
        }
        if ($this->arachno->crawlIsForUpdates($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/Legislation\.asp/')) {
                $this->handleUpdatesForLegislations($pageCrawl);
            }

            if ($this->arachno->matchUrl($pageCrawl, '/BulletinOfficiel\.aspx/')) {
                $this->handleUpdatesForActs($pageCrawl);
            }

            if ($this->arachno->matchUrl($pageCrawl, '/\.pdf/')) {
                if ($this->arachno->matchUrl($pageCrawl, '/fr\./i')) {
                    $pageCrawl->setOcrSettings(['languages' => ['fra']]);
                    $this->arachno->capturePDF($pageCrawl);
                }
                $pageCrawl->setOcrSettings(['provider' => 'pdfocr', 'languages' => ['ara']]);
                $this->arachno->capturePDF($pageCrawl);
            }
        }
    }

    public function preFetch(PageCrawl $pageCrawl): void
    {
        if ($this->arachno->crawlIsFullCatalogue($pageCrawl)) {
            if ($this->arachno->matchUrl($pageCrawl, '/arabe\/textesimportants\.aspx/')) {
                $pageCrawl->setProxySettings([
                    'provider' => 'scraping_bee',
                    'options' => [
                        'render_js' => true,
                        'wait_for' => 'div#Agg3120_Accordion h3#ui-id-6',
                        'js_scenario' => [
                            'instructions' => [
                                ['evaluate' => 'document.querySelectorAll("div#Agg3120_Accordion h3#ui-id-6").forEach((el) => el.click())'],
                                ['wait' => 3000],
                            ],
                        ],
                    ],
                ]);
            }

            if ($this->arachno->matchUrl($pageCrawl, '/arabe\/DesktopModules\/MVC\/TableListBO\/BO\/AjaxMethod\?/')) {
                $headers = [
                    'moduleId' => '3111',
                    'tabId' => '847',
                ];
                $this->handleBulletinPayload($pageCrawl, $headers, 'http://www.sgg.gov.ma/arabe/BulletinOfficiel.aspx');
            }

            if ($this->arachno->matchUrl($pageCrawl, '/DesktopModules\/MVC\/TableListBO\/BO\/AjaxMethod\?/')) {
                $headers = [
                    'moduleId' => '2873',
                    'tabId' => '775',
                ];
                $this->handleBulletinPayload($pageCrawl, $headers, 'http://www.sgg.gov.ma/BulletinOfficiel.aspx');
            }

            if ($this->arachno->matchUrl($pageCrawl, '/arabe\/textesconsolides\.aspx/')) {
                $pageCrawl->setProxySettings([
                    'provider' => 'scraping_bee',
                    'options' => [
                        'render_js' => true,
                        'wait_for' => 'div#Agg3255_Accordion h3#ui-id-4',
                        'js_scenario' => [
                            'instructions' => [
                                ['evaluate' => 'document.querySelectorAll("div#Agg3255_Accordion h3#ui-id-4").forEach((el) => el.click())'],
                                ['wait' => 3000],
                            ],
                        ],
                    ],
                ]);
            }
        }

        if ($this->arachno->matchUrl($pageCrawl, '/sgg\.gov\.ma\/BO/')) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => false,
                ],
            ]);
        }

        if ($this->arachno->crawlIsForUpdates($pageCrawl) && $this->arachno->matchUrl($pageCrawl, '/BulletinOfficiel\.aspx/')) {
            $pageCrawl->setProxySettings([
                'provider' => 'scraping_bee',
                'options' => [
                    'render_js' => true,
                    'wait_browser' => 'networkidle0',
                    'wait_for' => 'table[id="2873"] tbody tr[role="row"] a[href]',
                ],
            ]);
        }
    }

    /**
     * @param PageCrawl     $pageCrawl
     * @param array<string> $headers
     * @param string        $url
     *
     * @return void
     */
    protected function handleBulletinPayload(PageCrawl $pageCrawl, array $headers, string $url): void
    {
        /** @var DOMNode $html */
        $html = file_get_contents($url);
        $crawler = new DomCrawler($html);

        /** @var DOMElement $verificationToken */
        $verificationToken = $crawler->filter('input[name="__RequestVerificationToken"]')->getNode(0);
        $verificationToken = $verificationToken->getAttribute('value');

        $settings = [
            'method' => 'GET',
            'options' => [
                'headers' => [
                    'Moduleid' => $headers['moduleId'],
                    'Tabid' => $headers['tabId'],
                    'Accept' => 'application/json',
                    'Requestverificationtoken' => $verificationToken,
                ],
            ],
        ];

        $pageCrawl->setHttpSettings($settings);
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleCatalogue(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'div.ui-accordion-content.ui-corner-bottom.ui-helper-reset.ui-widget-content.ui-accordion-content-active ul li a[href], div.ui-accordion-content.ui-corner-bottom.ui-helper-reset.ui-widget-content.ui-accordion-content-active div p a[href]', function ($items) use ($pageCrawl) {
            $href = $items->getAttribute('href');
            $startUrl = !str_contains($href, 'sgg.gov.ma') ? $this->baseDomain . $href : $href;
            $uniqueId = Str::of($href)->afterLast('/')->before('.pdf');
            $title = $items->textContent ?? '';
            $catDoc = new CatalogueDoc([
                'title' => $title,
                'start_url' => $startUrl,
                'view_url' => $startUrl,
                'source_unique_id' => $uniqueId,
                'language_code' => 'ara',
                'primary_location_id' => 173834,
            ]);

            $this->arachno->createCatalogueDoc($pageCrawl, $catDoc);
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleUpdatesForActs(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'table[id="2873"] tbody tr[role="row"] a[href]', function ($items) use ($pageCrawl) {
            $dom = new DomCrawler($items?->parentNode?->parentNode?->parentNode);
            $link = $dom->filter('a[href]')->getNode(0);
            /** @var DOMElement $link */
            $href = $link->getAttribute('href');
            $pdfLink = str_contains($href, 'http://www.sgg.gov.ma') ? $href : $this->baseDomain . $href;
            $uniqueId = Str::of($href)->afterLast('/')->before('.pdf');
            $title = $dom->filter('td:nth-child(1)')->getNode(0)?->textContent ?? '';
            $date = $dom->filter('td:nth-child(2)')->getNode(0)?->textContent ?? '';
            $docMeta = new DocMetaDto();
            $docMeta->work_number = $uniqueId;
            $docMeta->source_unique_id = 'updates_' . $uniqueId;
            $docMeta->title = 'Bulletin Officiel ' . $title;
            $docMeta->language_code = 'ara';
            $docMeta->primary_location = '173834';
            $docMeta->work_type = 'act';
            $docMeta->work_date = Carbon::parse($date);
            $docMeta->source_url = $pdfLink;

            $l = new UrlFrontierLink(['url' => $pdfLink, 'anchor_text' => 'PDF']);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleUpdatesForLegislations(PageCrawl $pageCrawl): void
    {
        $this->arachno->each($pageCrawl, 'div.TextesRegissantLeSGGContainer ul li a[href]', function (DOMElement $items) use ($pageCrawl) {
            $dom = new DomCrawler($items->parentNode);
            $link = $dom->filter('a[href]')->getNode(0);
            /** @var DOMElement $link */
            $href = $link->getAttribute('href');
            $pdfLink = str_contains($href, 'http://www.sgg.gov.ma') ? $href : $this->baseDomain . $href;
            $uniqueId = Str::of($href)->afterLast('/')->before('.pdf');
            $docMeta = new DocMetaDto();
            $docMeta->work_number = $uniqueId;
            $docMeta->source_unique_id = 'updates_' . $uniqueId;
            $docMeta->title = $link->textContent ?? '';
            $docMeta->language_code = 'ara';
            $docMeta->primary_location = '173834';
            $docMeta->work_type = 'legislation';
            $docMeta->source_url = $pdfLink;
            $l = new UrlFrontierLink(['url' => $pdfLink, 'anchor_text' => 'PDF']);
            $l->_metaDto = $docMeta;
            $this->arachno->followLink($pageCrawl, $l, true);
        });
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return void
     */
    protected function handleGeneralBulletinCatalogue(PageCrawl $pageCrawl): void
    {
        $meta = [];
        $json = $pageCrawl->getJson();
        foreach ($json as $item) {
            $title = $item['BoNum'];
            $url = str_contains($item['BoUrl'], 'http://www.sgg.gov.ma/') ? $item['BoUrl'] : "http://www.sgg.gov.ma{$item['BoUrl']}";

            $date = $item['BoDate'];
            $date = Str::of($date)->after('Date(')->before(')/')->toString();
            /** @var string $date */
            $date = (int) $date / 1000;

            try {
                $date = Carbon::parse($date);
                $meta['work_date'] = $date;
                $meta['effective_date'] = $date;
            } catch (InvalidFormatException) {
            }

            $uniqueId = Str::of(strtolower($url))->afterLast('/')->beforeLast('.pdf');
            //            $uniqueId = str_contains(strtolower($uniqueId), '.pdf') ? Str::of($uniqueId)->beforeLast('.pdf') : $uniqueId;

            $catalogueDoc = new CatalogueDoc([
                'title' => 'Bulletin Officiel ' . $title,
                'source_unique_id' => $uniqueId,
                'start_url' => $url,
                'view_url' => $url,
                'language_code' => 'ara',
                'primary_location_id' => 173834,
            ]);

            $catDoc = $this->arachno->createCatalogueDoc($pageCrawl, $catalogueDoc);
            CatalogueDocMeta::updateOrCreate(['catalogue_doc_id' => $catDoc->id], [
                'doc_meta' => $meta,
            ]);
        }
    }

    /**
     * @param PageCrawl $pageCrawl
     *
     * @return Doc
     */
    protected function handleMeta(PageCrawl $pageCrawl): Doc
    {
        /** @var CatalogueDoc */
        $catalogueDoc = $pageCrawl->pageUrl->catalogueDoc;
        $catalogueDoc->load('docMeta');

        $doc = $this->arachno->setDocMetaProperty($pageCrawl, 'source_unique_id', $catalogueDoc->source_unique_id);
        $this->arachno->setDocMetaProperty($pageCrawl, 'language_code', 'ara');
        $this->arachno->setDocMetaProperty($pageCrawl, 'title_translation', $catalogueDoc->title_translation);
        $this->arachno->setDocMetaProperty($pageCrawl, 'title', $catalogueDoc->title);
        $this->arachno->setDocMetaProperty($pageCrawl, 'primary_location', 173834);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_type', (string) 'law');
        $this->arachno->setDocMetaProperty($pageCrawl, 'source_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'download_url', $catalogueDoc->start_url);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_number', $catalogueDoc->source_unique_id);

        $this->arachno->setDocMetaProperty($pageCrawl, 'effective_date', $catalogueDoc->docMeta?->doc_meta['effective_date'] ?? null);
        $this->arachno->setDocMetaProperty($pageCrawl, 'work_date', $catalogueDoc->docMeta?->doc_meta['work_date'] ?? null);

        return $doc;
    }

    /**
     * @return array<string,array<UrlFrontierLink>>
     */
    protected function getArabeMoroccoStartUrls(): array
    {
        $timestamp = (int) round(microtime(true) * 1000);

        return [
            'type_' . CrawlType::FULL_CATALOGUE->value => [
                new UrlFrontierLink(['url' => 'http://www.sgg.gov.ma/arabe/textesimportants.aspx']),
                new UrlFrontierLink(['url' => 'http://www.sgg.gov.ma/arabe/textesconsolides.aspx']),
                new UrlFrontierLink(['url' => "http://www.sgg.gov.ma/arabe/DesktopModules/MVC/TableListBO/BO/AjaxMethod?_={$timestamp}"]),
                new UrlFrontierLink(['url' => "http://www.sgg.gov.ma/DesktopModules/MVC/TableListBO/BO/AjaxMethod?_={$timestamp}"]),
            ],
            'type_' . CrawlType::FOR_UPDATES->value => [
                new UrlFrontierLink(['url' => 'http://www.sgg.gov.ma/BulletinOfficiel.aspx']),
                new UrlFrontierLink(['url' => 'http://www.sgg.gov.ma/Legislation.aspx#Agg2615_2']),
            ],
        ];
    }
}
